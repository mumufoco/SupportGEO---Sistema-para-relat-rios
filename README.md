# GeoSPT - Sistema de Gerenciamento de Sondagens SPT

Sistema completo para gestão de sondagens SPT (Standard Penetration Test) conforme normas NBR brasileiras, desenvolvido com CodeIgniter 4, PostgreSQL/PostGIS, Redis e MinIO.

## 🚀 Início Rápido

### Pré-requisitos

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

### Instalação

1. Clone o repositório:
```bash
git clone https://github.com/mumufoco/SupportGEO.git
cd SupportGEO
```

2. Configure o ambiente:
```bash
cp .env.example .env
```

3. Gere as chaves de segurança:
```bash
# JWT Secret Key
php -r 'echo bin2hex(random_bytes(32)) . PHP_EOL;'

# Encryption Key
openssl rand -hex 32
```

Atualize as chaves no arquivo `.env`.

4. Inicie os serviços:
```bash
docker compose up --build -d
```

5. Execute as migrations:
```bash
docker compose exec app php spark migrate
```

6. Acesse a aplicação:
- **Aplicação**: http://localhost:8080
- **MinIO Console**: http://localhost:9001 (minioadmin/minioadmin)

Para instruções detalhadas, consulte: **[docs/PHASE_0_SETUP.md](docs/PHASE_0_SETUP.md)**

## 🏗️ Arquitetura

### Stack Tecnológica

- **Backend**: PHP 8.2 + CodeIgniter 4
- **Banco de Dados**: PostgreSQL 15 + PostGIS 3.4
- **Cache/Filas**: Redis 7
- **Armazenamento**: MinIO (S3-compatible)
- **Web Server**: Nginx
- **Containers**: Docker + Docker Compose

### Serviços

| Serviço | Porta | Descrição |
|---------|-------|-----------|
| Nginx | 8080 | Web server |
| PostgreSQL/PostGIS | 5432 | Banco de dados com extensões espaciais |
| Redis | 6379 | Cache e sistema de filas |
| MinIO API | 9000 | Armazenamento de objetos (API) |
| MinIO Console | 9001 | Interface web do MinIO |

## 📚 Documentação

### Plano de Elaboração

O projeto segue um plano de desenvolvimento em fases:

- **[Fase 0 - Preparação do Ambiente](Plano%20de%20Elaboração/01_FASE_0_PREPARACAO.md)** ✅
- **[Fase 1 - Estrutura do Banco de Dados](Plano%20de%20Elaboração/02_FASE_1_BANCO_DADOS.md)**
- **[Fase 2 - Models e Repositories](Plano%20de%20Elaboração/03_FASE_2_MODELS_REPOSITORIES.md)**
- **[Fase 3 - Bibliotecas NBR](Plano%20de%20Elaboração/04_FASE_3_BIBLIOTECAS_NBR.md)**
- **[Fase 4 - PDF Service](Plano%20de%20Elaboração/05_FASE_4_PDF_SERVICE.md)**
- **[Fase 5 - API REST](Plano%20de%20Elaboração/06_FASE_5_API_REST.md)**
- **[Fase 6 - Interface](Plano%20de%20Elaboração/07_FASE_6_INTERFACE.md)**
- **[Fase 7 - Fotos e Importação](Plano%20de%20Elaboração/08_FASE_7_FOTOS_IMPORTACAO.md)**
- **[Fase 8 - Testes](Plano%20de%20Elaboração/09_FASE_8_TESTES.md)**
- **[Fase 9 - Deploy](Plano%20de%20Elaboração/10_FASE_9_DEPLOY.md)**

### Guias Técnicos

- **[Setup Inicial (Fase 0)](docs/PHASE_0_SETUP.md)** - Configuração do ambiente de desenvolvimento
- Mais documentação será adicionada nas próximas fases

## 🔧 Comandos Úteis

### Docker

```bash
# Iniciar serviços
docker compose up -d

# Ver logs
docker compose logs -f app

# Parar serviços
docker compose down

# Reconstruir containers
docker compose up --build -d

# Acessar shell do container
docker compose exec app bash
```

### CodeIgniter

```bash
# Migrations
docker compose exec app php spark migrate
docker compose exec app php spark migrate:rollback

# Seeders
docker compose exec app php spark db:seed InitialDataSeeder

# Cache
docker compose exec app php spark cache:clear

# Rotas
docker compose exec app php spark routes
```

### Composer

```bash
# Instalar dependências
docker compose exec app composer install

# Atualizar dependências
docker compose exec app composer update

# Autoload
docker compose exec app composer dump-autoload
```

### Testes

```bash
# Executar testes
docker compose exec app vendor/bin/phpunit

# Executar teste específico
docker compose exec app vendor/bin/phpunit --filter TestClassName
```

## 🔒 Segurança

⚠️ **IMPORTANTE**: As configurações padrão são apenas para desenvolvimento!

Para ambientes de produção:

1. Altere todas as senhas padrão
2. Gere chaves JWT e de criptografia fortes
3. Use HTTPS/SSL para todas as conexões
4. Configure firewall e segurança de rede adequados
5. Use gerenciamento de secrets (e.g., Docker Secrets, Vault)
6. Não exponha portas desnecessárias publicamente

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Licença

Este projeto é propriedade de Support Solo Sondagens Ltda.

## 📞 Suporte

Para suporte e questões técnicas, entre em contato através das issues do GitHub.

---

**© 2025 Support Solo Sondagens Ltda**
