#!/bin/bash

# PostgreSQL Restore Script for GeoSPT Manager
# Usage: ./scripts/restore-postgres.sh <backup_file>

set -e

if [ -z "$1" ]; then
    echo "Usage: $0 <backup_file>"
    echo "Example: $0 backups/postgres_geospt_db_20250110_120000.sql.gz"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "✗ Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Load environment variables
if [ -f "$PROJECT_DIR/.env" ]; then
    export $(cat "$PROJECT_DIR/.env" | grep -v '^#' | xargs)
fi

# Configuration
DB_HOST=${database_default_hostname:-localhost}
DB_PORT=${database_default_port:-5432}
DB_NAME=${database_default_database:-geospt_db}
DB_USER=${database_default_username:-geospt_user}
DB_PASSWORD=${database_default_password}

echo "================================================"
echo "PostgreSQL Restore Script"
echo "================================================"
echo ""
echo "Database: $DB_NAME"
echo "Host: $DB_HOST:$DB_PORT"
echo "Backup file: $BACKUP_FILE"
echo ""

# Warning
read -p "⚠️  This will OVERWRITE the current database. Continue? (yes/no): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled"
    exit 0
fi

# Export password
export PGPASSWORD="$DB_PASSWORD"

# Decompress if needed
RESTORE_FILE="$BACKUP_FILE"
if [[ "$BACKUP_FILE" == *.gz ]]; then
    echo "Decompressing backup..."
    RESTORE_FILE="${BACKUP_FILE%.gz}"
    gunzip -c "$BACKUP_FILE" > "$RESTORE_FILE"
    echo "✓ Backup decompressed"
fi

echo ""
echo "Starting restore..."

# Drop and recreate database
if command -v docker-compose &> /dev/null && [ -f "$PROJECT_DIR/docker-compose.yml" ]; then
    # Using Docker
    echo "Using Docker container for restore..."
    
    docker-compose exec -T db psql -U "$DB_USER" -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME;"
    docker-compose exec -T db psql -U "$DB_USER" -d postgres -c "CREATE DATABASE $DB_NAME;"
    
    # Restore
    cat "$RESTORE_FILE" | docker-compose exec -T db psql -U "$DB_USER" -d "$DB_NAME"
else
    # Using local PostgreSQL
    echo "Using local PostgreSQL for restore..."
    
    psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME;"
    psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d postgres -c "CREATE DATABASE $DB_NAME;"
    
    # Restore
    psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" < "$RESTORE_FILE"
fi

if [ $? -eq 0 ]; then
    echo "✓ Database restored successfully"
    
    # Clean up temporary file if decompressed
    if [[ "$BACKUP_FILE" == *.gz ]]; then
        rm "$RESTORE_FILE"
    fi
else
    echo "✗ Restore failed"
    exit 1
fi

# Run migrations to ensure schema is up to date
echo ""
echo "Running migrations..."
cd "$PROJECT_DIR"
php spark migrate
echo "✓ Migrations completed"

# Create restore log
LOG_FILE="$PROJECT_DIR/writable/logs/restore.log"
echo "$(date) - PostgreSQL restore completed from: $BACKUP_FILE" >> "$LOG_FILE"

echo ""
echo "================================================"
echo "Restore completed successfully!"
echo "================================================"

unset PGPASSWORD

exit 0
