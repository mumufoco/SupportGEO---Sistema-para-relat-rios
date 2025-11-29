# FASE 9: DEPLOY E PRODUÇÃO

**Tempo estimado:** 2-3 dias  
**Objetivo:** Configurar ambiente de produção e realizar deploy

---

## 🎯 Objetivos

- Configurar Docker para deploy
- Configurar Nginx como servidor web
- Implementar SSL/TLS
- Configurar backup automático
- Monitoramento e logs

---

## 🐳 DOCKERFILE

Criar `Dockerfile`:

```dockerfile
# GeoSPT Manager - Dockerfile
FROM php:8.2-fpm

# Argumentos
ARG DEBIAN_FRONTEND=noninteractive

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libexif-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . .

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/writable

# Copiar configurações
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Expor porta
EXPOSE 80

# Comando de inicialização
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

---

## 🐳 DOCKER COMPOSE

Criar `docker-compose.yml`:

```yaml
version: '3.8'

services:
  # Aplicação PHP
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: geospt_app
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./writable/uploads:/var/www/html/writable/uploads
      - ./writable/logs:/var/www/html/writable/logs
    environment:
      - CI_ENVIRONMENT=production
      - database.default.hostname=db
      - database.default.database=${DB_DATABASE}
      - database.default.username=${DB_USERNAME}
      - database.default.password=${DB_PASSWORD}
      - JWT_SECRET_KEY=${JWT_SECRET_KEY}
    depends_on:
      - db
    networks:
      - geospt_network

  # Banco de Dados MySQL
  db:
    image: mysql:8.0
    container_name: geospt_db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    networks:
      - geospt_network

  # phpMyAdmin (opcional - apenas desenvolvimento)
  phpmyadmin:
    image: phpmyadmin:latest
    container_name: geospt_pma
    restart: unless-stopped
    environment:
      PMA_HOST: db
      PMA_USER: ${DB_USERNAME}
      PMA_PASSWORD: ${DB_PASSWORD}
    ports:
      - "8080:80"
    depends_on:
      - db
    networks:
      - geospt_network
    profiles:
      - dev

volumes:
  mysql_data:

networks:
  geospt_network:
    driver: bridge
```

---

## ⚙️ CONFIGURAÇÃO NGINX

Criar `docker/nginx.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    # Upload máximo
    client_max_body_size 20M;

    # Headers de segurança
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Localização principal
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Arquivos estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Bloquear acesso a arquivos sensíveis
    location ~ /\. {
        deny all;
    }

    location ~ ^/(env|\.env|composer\.(json|lock)|package\.json) {
        deny all;
    }

    # Bloquear diretório writable
    location ~ ^/writable {
        deny all;
    }
}
```

---

## ⚙️ CONFIGURAÇÃO SUPERVISOR

Criar `docker/supervisord.conf`:

```ini
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/var/log/nginx/stdout.log
stderr_logfile=/var/log/nginx/stderr.log

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
autostart=true
autorestart=true
stdout_logfile=/var/log/php/stdout.log
stderr_logfile=/var/log/php/stderr.log
```

---

## 📋 SCRIPT DE DEPLOY

Criar `deploy.sh`:

```bash
#!/bin/bash

# ==========================================
# GeoSPT Manager - Script de Deploy
# ==========================================

set -e

echo "🚀 Iniciando deploy do GeoSPT Manager..."

# Variáveis
APP_DIR="/var/www/geospt-manager"
BACKUP_DIR="/var/backups/geospt"
DATE=$(date +%Y%m%d_%H%M%S)

# Criar backup do banco
echo "📦 Criando backup do banco de dados..."
mkdir -p $BACKUP_DIR
docker exec geospt_db mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Backup dos uploads
echo "📦 Backup dos uploads..."
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz $APP_DIR/writable/uploads

# Pull das alterações
echo "📥 Atualizando código..."
cd $APP_DIR
git pull origin main

# Atualizar dependências
echo "📦 Atualizando dependências..."
docker exec geospt_app composer install --no-dev --optimize-autoloader

# Executar migrations
echo "🗄️ Executando migrations..."
docker exec geospt_app php spark migrate

# Limpar cache
echo "🧹 Limpando cache..."
docker exec geospt_app php spark cache:clear

# Reiniciar containers
echo "🔄 Reiniciando serviços..."
docker-compose restart app

# Verificar status
echo "✅ Verificando status..."
docker-compose ps

echo "🎉 Deploy concluído com sucesso!"
echo "   Backup do banco: $BACKUP_DIR/db_$DATE.sql.gz"
echo "   Backup uploads: $BACKUP_DIR/uploads_$DATE.tar.gz"
```

---

## 🔒 CONFIGURAÇÃO SSL COM CERTBOT

```bash
# Comando 1: Instalar Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# Comando 2: Obter certificado
sudo certbot --nginx -d geospt.supportsondagens.com.br

# Comando 3: Renovação automática
sudo certbot renew --dry-run

# Comando 4: Adicionar ao cron
echo "0 0 * * * /usr/bin/certbot renew --quiet" | sudo tee -a /etc/crontab
```

---

## 📦 BACKUP AUTOMÁTICO

Criar `cron/backup.sh`:

```bash
#!/bin/bash

# ==========================================
# Script de Backup Automático
# Executar diariamente via cron
# ==========================================

BACKUP_DIR="/var/backups/geospt"
DATE=$(date +%Y%m%d)
RETENTION_DAYS=30

# Criar diretório se não existir
mkdir -p $BACKUP_DIR

# Backup do banco de dados
echo "Backup do banco de dados..."
docker exec geospt_db mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_DATABASE} | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup dos uploads
echo "Backup dos uploads..."
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /var/www/geospt-manager/writable/uploads

# Remover backups antigos
echo "Removendo backups antigos..."
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete

# Log
echo "[$(date)] Backup concluído" >> /var/log/geospt_backup.log
```

Adicionar ao crontab:

```bash
# Backup diário às 3:00
0 3 * * * /opt/scripts/backup.sh
```

---

## 📊 MONITORAMENTO

### Health Check Endpoint

Adicionar em `app/Controllers/Api/HealthController.php`:

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class HealthController extends ResourceController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        try {
            $db->query('SELECT 1');
            $dbStatus = 'ok';
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        return $this->respond([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'services' => [
                'database' => $dbStatus,
                'uploads' => is_writable(WRITEPATH . 'uploads') ? 'ok' : 'error',
                'logs' => is_writable(WRITEPATH . 'logs') ? 'ok' : 'error',
            ],
        ]);
    }
}
```

---

## 📝 CHECKLIST DE SEGURANÇA PRODUÇÃO

```markdown
## Checklist de Segurança

### Servidor
- [ ] Firewall configurado (UFW)
- [ ] SSH apenas com chave
- [ ] Fail2ban instalado
- [ ] Atualizações automáticas

### Aplicação
- [ ] CI_ENVIRONMENT = production
- [ ] Debug desabilitado
- [ ] Chaves JWT/Encryption seguras
- [ ] HTTPS obrigatório
- [ ] Headers de segurança
- [ ] CORS configurado

### Banco de Dados
- [ ] Senhas fortes
- [ ] Acesso apenas local
- [ ] Backup automático
- [ ] Logs habilitados

### Arquivos
- [ ] Permissões corretas (755/644)
- [ ] Upload validado
- [ ] Diretório writable protegido
```

---

## 📝 COMANDOS DE PRODUÇÃO

```bash
# Comando 1: Build da imagem
docker-compose build

# Comando 2: Iniciar em produção
docker-compose up -d

# Comando 3: Ver logs
docker-compose logs -f app

# Comando 4: Executar migrations
docker exec geospt_app php spark migrate

# Comando 5: Limpar cache
docker exec geospt_app php spark cache:clear

# Comando 6: Status dos containers
docker-compose ps

# Comando 7: Reiniciar serviços
docker-compose restart

# Comando 8: Parar tudo
docker-compose down
```

---

## ✅ CHECKLIST FASE 9

- [ ] Dockerfile criado e testado
- [ ] Docker Compose configurado
- [ ] Nginx configurado
- [ ] SSL/TLS habilitado
- [ ] Backup automático configurado
- [ ] Health check endpoint
- [ ] Script de deploy
- [ ] Monitoramento configurado
- [ ] Checklist de segurança verificado
- [ ] Documentação atualizada

---

## 🎉 SISTEMA COMPLETO

Parabéns! O sistema GeoSPT Manager está pronto para produção.

### Resumo do Sistema

| Componente | Status |
|------------|--------|
| Backend PHP/CI4 | ✅ |
| Banco MySQL | ✅ |
| API REST | ✅ |
| Geração PDF | ✅ |
| Upload Fotos | ✅ |
| Importação Excel | ✅ |
| Interface Web | ✅ |
| Testes | ✅ |
| Deploy Docker | ✅ |

### URLs de Acesso

- **Aplicação:** https://geospt.supportsondagens.com.br
- **API:** https://geospt.supportsondagens.com.br/api
- **Health Check:** https://geospt.supportsondagens.com.br/api/health

---

**© 2025 Support Solo Sondagens Ltda**
