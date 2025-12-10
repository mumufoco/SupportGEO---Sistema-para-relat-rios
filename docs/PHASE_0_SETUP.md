# PHASE 0 SETUP - Docker Development Environment

This document provides step-by-step instructions for setting up the GeoSPT development environment using Docker with PostgreSQL/PostGIS, Redis, and MinIO.

---

## 📋 Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

---

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/mumufoco/SupportGEO.git
cd SupportGEO
```

### 2. Copy Environment Configuration

```bash
cp .env.example .env
```

### 3. Generate Security Keys

#### Generate JWT Secret Key
```bash
php -r 'echo bin2hex(random_bytes(32)) . PHP_EOL;'
```

Copy the generated key and update `JWT_SECRET_KEY` in your `.env` file.

#### Generate Encryption Key
```bash
openssl rand -hex 32
```

Copy the generated key and update `encryption.key` in your `.env` file with the format:
```ini
encryption.key = hex2bin:YOUR_GENERATED_KEY_HERE
```

### 4. Start Docker Services

```bash
docker compose up --build -d
```

This will start the following services:
- **PostgreSQL/PostGIS** (port 5432) - Database with spatial extensions
- **Redis** (port 6379) - Cache and queue system
- **MinIO** (ports 9000, 9001) - S3-compatible object storage
- **PHP-FPM** - Application runtime
- **Nginx** (port 8080) - Web server
- **Worker** (optional) - Background job processor

### 5. Wait for Services to be Ready

Check services health status:
```bash
docker compose ps
```

All services should show "healthy" status.

### 6. Run Database Migrations

```bash
docker compose exec app php spark migrate
```

### 7. (Optional) Seed Initial Data

If you have a seeder file:
```bash
docker compose exec app php spark db:seed InitialDataSeeder
```

### 8. Configure MinIO Bucket

Access MinIO Console at http://localhost:9001

**Default credentials:**
- Username: `minioadmin`
- Password: `minioadmin`

Create a new bucket named `geospt` (or the name specified in your `.env` file).

### 9. Access the Application

Open your browser and navigate to:
```
http://localhost:8080
```

---

## 🔧 Common Commands

### View Application Logs
```bash
docker compose logs -f app
```

### View All Services Logs
```bash
docker compose logs -f
```

### Access Application Container Shell
```bash
docker compose exec app bash
```

### Access PostgreSQL CLI
```bash
docker compose exec db psql -U geospt_user -d geospt_db
```

### Access Redis CLI
```bash
docker compose exec redis redis-cli
```

### Run Composer Commands
```bash
docker compose exec app composer install
docker compose exec app composer update
```

> **Note on Vendor Directory**: This repository supports versioning the `vendor/` directory for deployment scenarios requiring guaranteed dependency availability. See **[docs/COMMIT_VENDOR.md](COMMIT_VENDOR.md)** for safe vendor commit procedures, including size checks, license verification, and Git LFS recommendations.

### Run PHPUnit Tests
```bash
docker compose exec app vendor/bin/phpunit
```

### Stop All Services
```bash
docker compose down
```

### Stop and Remove Volumes (⚠️ This will delete all data)
```bash
docker compose down -v
```

### Rebuild Containers
```bash
docker compose up --build -d
```

---

## 🗄️ Database Configuration

### PostgreSQL/PostGIS

The database service uses the official PostGIS image which includes:
- PostgreSQL 15
- PostGIS 3.4 (spatial extension)

**Default configuration:**
- Host: `db` (within Docker network) or `localhost` (from host)
- Port: `5432`
- Database: `geospt_db`
- Username: `geospt_user`
- Password: `SenhaSegura@2025` (⚠️ Change in production!)

### Connecting from Host Machine

You can connect to PostgreSQL from your host machine using any PostgreSQL client:

```bash
psql -h localhost -p 5432 -U geospt_user -d geospt_db
```

### PostGIS Extensions

PostGIS is already installed in the database. To verify:

```sql
SELECT PostGIS_version();
```

To enable PostGIS in your database (if not already enabled):

```sql
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;
```

---

## 📦 Object Storage (MinIO)

MinIO provides S3-compatible object storage for file uploads.

### Access MinIO Console
- **URL:** http://localhost:9001
- **Username:** `minioadmin`
- **Password:** `minioadmin`

### MinIO API Endpoint
- **URL:** http://localhost:9000

### Creating Buckets

You can create buckets via:

1. **Web Console** (recommended for initial setup)
2. **MinIO Client (mc)**
3. **Application code** (S3-compatible SDK)

### Using MinIO Client

Install MinIO client:
```bash
# Linux/Mac
wget https://dl.min.io/client/mc/release/linux-amd64/mc
chmod +x mc
./mc alias set local http://localhost:9000 minioadmin minioadmin
./mc mb local/geospt
```

---

## 🔄 Redis Configuration

Redis is used for:
- **Caching** - Application cache
- **Queue** - Background job queue
- **Session Storage** - Session data (optional)

**Default configuration:**
- Host: `redis` (within Docker network) or `localhost` (from host)
- Port: `6379`
- No password (development only)

### Test Redis Connection

```bash
docker compose exec redis redis-cli ping
# Expected output: PONG
```

---

## 👷 Background Worker (Optional)

The worker service runs background jobs using Redis queue.

To start the worker:
```bash
docker compose --profile with-worker up -d worker
```

To view worker logs:
```bash
docker compose logs -f worker
```

---

## 🔒 Security Notes

### For Development

The `.env.example` file contains default credentials suitable for development only.

### For Production

⚠️ **IMPORTANT:** Before deploying to production:

1. **Change all default passwords:**
   - Database password
   - MinIO credentials
   - JWT secret key
   - Encryption key

2. **Use strong, randomly generated values:**
   ```bash
   # Generate strong passwords
   openssl rand -base64 32
   ```

3. **Never commit `.env` file to version control**

4. **Use environment-specific configurations**

5. **Enable SSL/TLS for all services**

6. **Configure proper network security and firewalls**

---

## 🐛 Troubleshooting

### Port Already in Use

If you get port binding errors:

```bash
# Check which process is using the port
sudo lsof -i :8080
sudo lsof -i :5432

# Change the port mapping in docker-compose.yml
# For example, change "8080:80" to "8081:80"
```

### Permission Denied on writable Directory

```bash
# Fix permissions
docker compose exec app chown -R www-data:www-data /var/www/html/writable
docker compose exec app chmod -R 775 /var/www/html/writable
```

### Database Connection Failed

1. Check if database service is healthy:
   ```bash
   docker compose ps db
   ```

2. Check database logs:
   ```bash
   docker compose logs db
   ```

3. Verify `.env` configuration matches docker-compose.yml

### Cannot Access Application

1. Check all services are running:
   ```bash
   docker compose ps
   ```

2. Check nginx logs:
   ```bash
   docker compose logs nginx
   ```

3. Check app logs:
   ```bash
   docker compose logs app
   ```

### Composer Dependencies Issues

```bash
# Clear composer cache and reinstall
docker compose exec app rm -rf vendor
docker compose exec app composer clear-cache
docker compose exec app composer install
```

---

## 📚 Additional Resources

- [CodeIgniter 4 Documentation](https://codeigniter.com/user_guide/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [PostGIS Documentation](https://postgis.net/documentation/)
- [MinIO Documentation](https://min.io/docs/)
- [Redis Documentation](https://redis.io/documentation)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

---

## 🔄 Next Steps

After completing Phase 0 setup:

1. ➡️ **[Phase 1 - Database Migrations](../Plano de Elaboração/02_FASE_1_BANCO_DADOS.md)**
2. Review database schema and models
3. Set up repositories and services
4. Implement API endpoints

---

**© 2025 Support Solo Sondagens Ltda**
