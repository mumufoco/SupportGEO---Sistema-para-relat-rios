# GeoSPT Manager

Sistema de gerenciamento de sondagens geotécnicas SPT (Standard Penetration Test) com suporte completo a PostGIS, processamento assíncrono e armazenamento de objetos.

## 🚀 Características

- **Backend**: CodeIgniter 4 + PHP 8.2
- **Banco de Dados**: PostgreSQL 15 + PostGIS 3.3
- **Cache e Filas**: Redis 7
- **Armazenamento**: MinIO (S3-compatible)
- **Workers**: Processamento assíncrono de PDFs, imagens e imports
- **API REST**: Autenticação JWT com refresh tokens
- **Geolocalização**: Suporte completo a coordenadas UTM e lat/long com SIRGAS 2000

## 📋 Pré-requisitos

- Docker >= 20.10
- Docker Compose >= 2.0
- PHP >= 8.2 (para desenvolvimento local)
- Composer >= 2.0

## 🛠️ Instalação e Configuração

### 1. Clone o repositório

```bash
git clone https://github.com/mumufoco/SupportGEO.git
cd SupportGEO
```

### 2. Configure o ambiente

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite o .env com suas configurações (opcional para dev)
nano .env
```

### 3. Instale as dependências

```bash
composer install
```

### 4. Inicie os serviços com Docker

```bash
docker-compose up -d
```

Isso iniciará:
- **PostgreSQL + PostGIS** na porta 5432
- **Redis** na porta 6379
- **MinIO API** na porta 9000
- **MinIO Console** na porta 9001
- **Aplicação** na porta 8080
- **Worker** (processamento em background)

### 5. Execute as migrations

```bash
# Dentro do container da aplicação
docker-compose exec app php spark migrate

# Ou localmente (se PHP estiver instalado)
php spark migrate
```

### 6. (Opcional) Popular com dados de exemplo

```bash
docker-compose exec app php spark db:seed ExampleSeeder
```

## 🌐 Acessando a Aplicação

- **Aplicação Web**: http://localhost:8080
- **MinIO Console**: http://localhost:9001 (admin/admin123)
- **PostgreSQL**: localhost:5432 (geospt_user/geospt_password)
- **Redis**: localhost:6379

## 📚 Estrutura do Projeto

```
.
├── app/
│   ├── Commands/           # Comandos CLI (workers)
│   ├── Config/             # Configurações
│   ├── Controllers/        # Controllers (API e Web)
│   ├── Database/
│   │   ├── Migrations/     # Migrations do banco
│   │   └── Seeds/          # Seeds de dados
│   ├── Libraries/
│   │   ├── Queue/          # Sistema de filas (Redis)
│   │   ├── Storage/        # Armazenamento S3/MinIO
│   │   └── NBR/            # Bibliotecas NBR
│   ├── Models/             # Models
│   ├── Repositories/       # Repositories
│   ├── Services/           # Services
│   ├── Views/              # Views
│   └── Workers/            # Workers para processamento assíncrono
├── docker/                 # Configurações Docker
│   ├── nginx/              # Nginx config
│   ├── supervisor/         # Supervisor config (workers)
│   ├── php/                # PHP config
│   ├── postgres/           # PostgreSQL init scripts
│   └── minio/              # MinIO documentation
├── public/                 # Arquivos públicos
├── tests/                  # Testes automatizados
├── Plano de Elaboração/    # Documentação detalhada
├── docker-compose.yml      # Docker Compose
├── Dockerfile              # Dockerfile
└── README.md               # Este arquivo
```

## 🔧 Comandos Úteis

### Workers

```bash
# Executar worker de PDF
docker-compose exec app php spark worker:run pdf

# Executar worker de imagens
docker-compose exec app php spark worker:run image

# Executar worker de imports
docker-compose exec app php spark worker:run import

# Executar todos os workers (dev only)
docker-compose exec app php spark worker:run all
```

### Migrations e Seeds

```bash
# Executar migrations
docker-compose exec app php spark migrate

# Reverter última migration
docker-compose exec app php spark migrate:rollback

# Executar seeds
docker-compose exec app php spark db:seed NomeDoSeeder

# Refresh (rollback all + migrate + seed)
docker-compose exec app php spark migrate:refresh
```

### Testes

```bash
# Executar todos os testes
docker-compose exec app ./vendor/bin/phpunit

# Executar testes específicos
docker-compose exec app ./vendor/bin/phpunit --filter NomeDoTeste

# Executar com coverage
docker-compose exec app ./vendor/bin/phpunit --coverage-html build/coverage
```

## 📡 API Endpoints

### Autenticação

```
POST   /api/auth/login              # Login
POST   /api/auth/refresh            # Refresh token
POST   /api/auth/logout             # Logout
```

### Sondagens

```
GET    /api/sondagens               # Listar sondagens
POST   /api/sondagens               # Criar sondagem
GET    /api/sondagens/{id}          # Obter sondagem
PUT    /api/sondagens/{id}          # Atualizar sondagem
DELETE /api/sondagens/{id}          # Deletar sondagem
```

### Fotos

```
GET    /api/sondagens/{id}/fotos                    # Listar fotos
POST   /api/sondagens/{id}/fotos/upload             # Upload de fotos
GET    /api/sondagens/{id}/fotos/presigned          # Gerar URL presigned
DELETE /api/fotos/{id}                              # Deletar foto
```

### PDFs e Relatórios

```
POST   /api/reports/sondagem/{id}/pdf               # Enfileirar geração de PDF
GET    /api/reports/sondagem/{id}/pdf/preview       # Preview do PDF (síncrono)
GET    /api/reports/{reportId}/download             # Download do PDF
```

### Jobs

```
GET    /api/jobs/{jobId}            # Status do job
GET    /api/jobs                    # Listar jobs
```

### Imports

```
POST   /api/imports                 # Importar planilha Excel
GET    /api/imports/template        # Download template
GET    /api/imports/{id}            # Status da importação
```

## 🔐 Autenticação

A API utiliza JWT (JSON Web Tokens) para autenticação:

```bash
# Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "senha": "senha123"
  }'

# Uso do token
curl -X GET http://localhost:8080/api/sondagens \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```

## 📦 Armazenamento (MinIO/S3)

### Buckets

- **geospt-uploads**: Fotos e documentos originais
- **geospt-pdfs**: PDFs gerados
- **geospt-thumbnails**: Miniaturas de imagens

### Presigned URLs

Para uploads grandes, use URLs presigned:

```javascript
// 1. Obter URL presigned
const response = await fetch('/api/sondagens/123/fotos/presigned', {
  headers: { 'Authorization': 'Bearer ' + token }
});
const { presignedUrl, s3Key } = await response.json();

// 2. Upload direto para S3/MinIO
await fetch(presignedUrl, {
  method: 'PUT',
  body: fileBlob,
  headers: { 'Content-Type': 'image/jpeg' }
});

// 3. Notificar a aplicação
await fetch('/api/sondagens/123/fotos/confirm', {
  method: 'POST',
  headers: { 
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ s3Key })
});
```

## 🗺️ PostGIS e Geometrias

O sistema utiliza PostGIS para armazenar e consultar dados geoespaciais:

```php
// Buscar sondagens próximas a um ponto
$repository->findNearby($latitude, $longitude, $radiusKm);

// Buscar sondagens dentro de uma área
$repository->findWithinBounds($minLat, $minLng, $maxLat, $maxLng);

// Converter coordenadas
$utm = $coordConverter->geographicToUTM($lat, $lng);
$geographic = $coordConverter->utmToGeographic($este, $norte, $zona);
```

## 🔄 Sistema de Filas

O sistema utiliza Redis para gerenciar filas de processamento:

```php
use App\Libraries\Queue\RedisQueue;

$queue = new RedisQueue();

// Enfileirar job
$jobId = $queue->push('pdf-generation', [
    'type' => 'pdf',
    'sondagem_id' => 123,
    'usuario_id' => 1,
]);

// Consultar status
$stats = $queue->getStats('pdf-generation');
// ['size' => 5, 'delayed' => 2, 'failed' => 1]
```

## 🐛 Debug e Logs

```bash
# Ver logs da aplicação
docker-compose logs -f app

# Ver logs do worker
docker-compose logs -f worker

# Ver logs do PostgreSQL
docker-compose logs -f db

# Acessar logs dentro do container
docker-compose exec app tail -f writable/logs/log-*.log
```

## 🚀 Deployment

### Produção com Docker

1. Crie um `.env.production` com configurações de produção
2. Use o `docker-compose.prod.yml` (a ser criado)
3. Configure SSL/TLS com Let's Encrypt
4. Configure backups automáticos

```bash
# Build para produção
docker-compose -f docker-compose.prod.yml build

# Deploy
docker-compose -f docker-compose.prod.yml up -d
```

### Backup

```bash
# Backup do PostgreSQL
docker-compose exec db pg_dump -U geospt_user geospt_db > backup.sql

# Backup do MinIO
docker run --rm -v minio_data:/data -v $(pwd):/backup alpine \
  tar czf /backup/minio-backup.tar.gz /data
```

### Restore

```bash
# Restore PostgreSQL
cat backup.sql | docker-compose exec -T db psql -U geospt_user geospt_db

# Restore MinIO
docker run --rm -v minio_data:/data -v $(pwd):/backup alpine \
  tar xzf /backup/minio-backup.tar.gz -C /
```

## 📝 Desenvolvimento

### Code Style

O projeto segue PSR-12. Use o PHP CS Fixer:

```bash
composer require --dev friendsofphp/php-cs-fixer
./vendor/bin/php-cs-fixer fix app/
```

### Testes

Escreva testes para novas funcionalidades:

```php
namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class SondagemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    
    public function testCriarSondagem()
    {
        // Seu teste aqui
    }
}
```

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 📧 Contato

- GitHub: [@mumufoco](https://github.com/mumufoco)
- Projeto: [https://github.com/mumufoco/SupportGEO](https://github.com/mumufoco/SupportGEO)

## 🙏 Agradecimentos

- CodeIgniter 4
- PostGIS
- MinIO
- Redis
- Intervention Image
- TCPDF
