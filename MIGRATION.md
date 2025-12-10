# Guia de Migração: MySQL para PostgreSQL/PostGIS

Este documento descreve o processo de migração de dados do MySQL para PostgreSQL com PostGIS para o sistema GeoSPT Manager.

## 📋 Pré-requisitos

- Backup completo do banco MySQL
- PostgreSQL 15+ com PostGIS 3.3+ instalado
- Ferramenta pgloader instalada (recomendada)
- Acesso aos dois bancos de dados simultaneamente

## 🔄 Opções de Migração

### Opção 1: Usando pgloader (Recomendado)

pgloader é uma ferramenta que facilita a migração de MySQL para PostgreSQL com conversão automática de tipos.

#### 1. Instalar pgloader

```bash
# Ubuntu/Debian
sudo apt-get install pgloader

# macOS
brew install pgloader

# Docker
docker pull dimitri/pgloader
```

#### 2. Criar arquivo de configuração pgloader

Crie um arquivo `migration.load`:

```lisp
LOAD DATABASE
    FROM mysql://geospt_user:senha@localhost/geospt_db
    INTO postgresql://geospt_user:senha@localhost/geospt_db

WITH include drop, create tables, create indexes, reset sequences

SET MySQL PARAMETERS
    net_read_timeout  = '120',
    net_write_timeout = '120'

SET PostgreSQL PARAMETERS
    maintenance_work_mem to '128MB',
    work_mem to '12MB'

CAST type datetime to timestamptz drop default drop not null using zero-dates-to-null,
     type date drop not null drop default using zero-dates-to-null

BEFORE LOAD DO
    $$ DROP SCHEMA IF EXISTS public CASCADE; $$,
    $$ CREATE SCHEMA public; $$,
    $$ CREATE EXTENSION IF NOT EXISTS postgis; $$,
    $$ CREATE EXTENSION IF NOT EXISTS pgcrypto; $$

AFTER LOAD DO
    $$ ALTER TABLE fotos ADD COLUMN localizacao geometry(Point,4674); $$,
    $$ UPDATE fotos SET localizacao = ST_SetSRID(ST_MakePoint(longitude, latitude), 4674) WHERE latitude IS NOT NULL AND longitude IS NOT NULL; $$,
    $$ CREATE INDEX idx_fotos_localizacao ON fotos USING GIST (localizacao); $$
;
```

#### 3. Executar migração

```bash
pgloader migration.load
```

### Opção 2: Dump e Restore Manual

#### 1. Fazer dump do MySQL

```bash
mysqldump -u geospt_user -p geospt_db > mysql_backup.sql
```

#### 2. Converter schema

O schema MySQL precisa ser convertido para PostgreSQL. Principais mudanças:

**Tipos de dados:**
- `AUTO_INCREMENT` → `SERIAL` ou `BIGSERIAL`
- `DATETIME` → `TIMESTAMP`
- `TINYINT(1)` → `BOOLEAN`
- `VARCHAR(255)` → Mantém `VARCHAR(255)`
- `TEXT` → `TEXT` ou `JSONB` (para JSON)
- `ENUM` → Criar tipos ENUM personalizados

**Sintaxe:**
- Aspas simples para strings
- Aspas duplas para identificadores
- `AUTOCOMMIT` não existe
- `ENGINE=InnoDB` removido

#### 3. Executar migrations do CodeIgniter

```bash
# Ao invés de importar o dump, use as migrations
php spark migrate
```

#### 4. Exportar dados do MySQL

```bash
# Exportar apenas dados (sem schema)
mysqldump -u geospt_user -p --no-create-info --skip-triggers geospt_db > data_only.sql
```

#### 5. Importar dados para PostgreSQL

Converta e importe os dados:

```bash
# Editar data_only.sql para ajustar sintaxe PostgreSQL
# Então importar:
psql -U geospt_user -d geospt_db < data_only_converted.sql
```

### Opção 3: Exportar/Importar via CSV

Para maior controle, exporte cada tabela como CSV e importe no PostgreSQL.

#### 1. Exportar do MySQL

```sql
-- Para cada tabela
SELECT * INTO OUTFILE '/tmp/empresas.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
FROM empresas;
```

#### 2. Importar no PostgreSQL

```sql
COPY empresas FROM '/tmp/empresas.csv' DELIMITER ',' CSV HEADER;
```

## 🔧 Ajustes Pós-Migração

### 1. Atualizar Sequences

```sql
-- Para cada tabela com ID serial
SELECT setval('empresas_id_seq', (SELECT MAX(id) FROM empresas));
SELECT setval('usuarios_id_seq', (SELECT MAX(id) FROM usuarios));
SELECT setval('projetos_id_seq', (SELECT MAX(id) FROM projetos));
-- ... repita para todas as tabelas
```

### 2. Criar Geometrias PostGIS

Se os dados não incluem geometrias, crie-as a partir de lat/long:

```sql
-- Para fotos
UPDATE fotos 
SET localizacao = ST_SetSRID(ST_MakePoint(longitude, latitude), 4674)
WHERE latitude IS NOT NULL AND longitude IS NOT NULL;

-- Para sondagens
UPDATE sondagens 
SET localizacao = ST_SetSRID(ST_MakePoint(longitude, latitude), 4674)
WHERE latitude IS NOT NULL AND longitude IS NOT NULL;

-- Para obras
UPDATE obras 
SET localizacao = ST_SetSRID(ST_MakePoint(longitude, latitude), 4674)
WHERE latitude IS NOT NULL AND longitude IS NOT NULL;
```

### 3. Criar Índices Espaciais

```sql
CREATE INDEX IF NOT EXISTS idx_fotos_localizacao ON fotos USING GIST (localizacao);
CREATE INDEX IF NOT EXISTS idx_sondagens_localizacao ON sondagens USING GIST (localizacao);
CREATE INDEX IF NOT EXISTS idx_obras_localizacao ON obras USING GIST (localizacao);
```

### 4. Converter ENUM para tipos PostgreSQL

Se usando ENUMs do MySQL, criar tipos correspondentes:

```sql
-- Verificar ENUMs no MySQL
SHOW COLUMNS FROM sondagens WHERE Type LIKE 'enum%';

-- Criar tipos no PostgreSQL (já feito nas migrations)
-- CREATE TYPE sondagem_status AS ENUM (...);
```

### 5. Atualizar JSONB

Se tinha campos TEXT com JSON no MySQL:

```sql
-- Converter TEXT para JSONB
ALTER TABLE fotos ALTER COLUMN metadata_exif TYPE JSONB USING metadata_exif::jsonb;
ALTER TABLE audit_log ALTER COLUMN dados_antigos TYPE JSONB USING dados_antigos::jsonb;
ALTER TABLE audit_log ALTER COLUMN dados_novos TYPE JSONB USING dados_novos::jsonb;
```

## ✅ Validação Pós-Migração

### 1. Verificar contagem de registros

```sql
-- MySQL
SELECT 
    table_name,
    table_rows
FROM information_schema.tables
WHERE table_schema = 'geospt_db';

-- PostgreSQL
SELECT 
    schemaname,
    tablename,
    n_live_tup as row_count
FROM pg_stat_user_tables
WHERE schemaname = 'public';
```

### 2. Verificar integridade referencial

```sql
-- Verificar foreign keys
SELECT 
    conname AS constraint_name,
    conrelid::regclass AS table_name,
    confrelid::regclass AS referenced_table
FROM pg_constraint
WHERE contype = 'f';
```

### 3. Verificar geometrias

```sql
-- Verificar se geometrias foram criadas corretamente
SELECT 
    COUNT(*) as total,
    COUNT(localizacao) as com_geometria,
    COUNT(*) - COUNT(localizacao) as sem_geometria
FROM fotos;

-- Validar geometrias
SELECT id, ST_IsValid(localizacao) as is_valid
FROM fotos
WHERE localizacao IS NOT NULL
AND NOT ST_IsValid(localizacao);
```

### 4. Testar queries espaciais

```sql
-- Buscar sondagens próximas a um ponto
SELECT 
    id,
    codigo_sondagem,
    ST_Distance(
        localizacao,
        ST_SetSRID(ST_MakePoint(-46.6333, -23.5505), 4674)
    ) / 1000 as distancia_km
FROM sondagens
WHERE localizacao IS NOT NULL
ORDER BY distancia_km
LIMIT 10;
```

## 🚨 Problemas Comuns e Soluções

### Problema: Charset/Encoding

```sql
-- Verificar encoding
SHOW client_encoding;

-- Mudar se necessário
SET client_encoding = 'UTF8';
```

### Problema: Datas inválidas

MySQL aceita '0000-00-00', PostgreSQL não:

```sql
-- Substituir datas inválidas
UPDATE table SET date_column = NULL WHERE date_column = '0000-00-00';
```

### Problema: AUTO_INCREMENT vs SERIAL

Após importar dados, sequences podem estar desatualizadas:

```bash
# Script para atualizar todas as sequences
psql -U geospt_user -d geospt_db << 'EOF'
DO $$
DECLARE
    r RECORD;
BEGIN
    FOR r IN
        SELECT 
            table_name,
            column_name
        FROM information_schema.columns
        WHERE table_schema = 'public'
        AND column_default LIKE 'nextval%'
    LOOP
        EXECUTE format(
            'SELECT setval(pg_get_serial_sequence(%L, %L), COALESCE(MAX(%I), 1)) FROM %I',
            r.table_name, r.column_name, r.column_name, r.table_name
        );
    END LOOP;
END $$;
EOF
```

### Problema: Performance lenta

```sql
-- Atualizar estatísticas
ANALYZE;

-- Reindexar
REINDEX DATABASE geospt_db;

-- Vacuum
VACUUM FULL ANALYZE;
```

## 📊 Checklist de Migração

- [ ] Backup completo do MySQL
- [ ] Instalar PostgreSQL + PostGIS
- [ ] Executar migrations do CodeIgniter
- [ ] Migrar dados (pgloader ou manual)
- [ ] Atualizar sequences
- [ ] Criar geometrias PostGIS
- [ ] Criar índices espaciais
- [ ] Converter ENUMs
- [ ] Atualizar JSONB
- [ ] Validar contagem de registros
- [ ] Verificar integridade referencial
- [ ] Testar geometrias
- [ ] Testar queries espaciais
- [ ] Executar ANALYZE
- [ ] Testar aplicação
- [ ] Atualizar `.env` com novas credenciais
- [ ] Backup do PostgreSQL

## 🔄 Rollback

Se necessário reverter:

```bash
# Restaurar MySQL
mysql -u geospt_user -p geospt_db < mysql_backup.sql

# Atualizar .env para MySQL
# database.default.DBDriver = MySQLi
```

## 📞 Suporte

Para problemas na migração, consulte:
- [Documentação PostgreSQL](https://www.postgresql.org/docs/)
- [Documentação PostGIS](https://postgis.net/docs/)
- [pgloader](https://pgloader.readthedocs.io/)
