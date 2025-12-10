#!/bin/bash

# PostgreSQL Backup Script for GeoSPT Manager
# Usage: ./scripts/backup-postgres.sh
# Can be added to crontab for automatic backups

set -e

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

BACKUP_DIR="$PROJECT_DIR/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/postgres_${DB_NAME}_${TIMESTAMP}.sql"
BACKUP_COMPRESSED="$BACKUP_FILE.gz"

# Retention settings (days)
RETENTION_DAYS=30

echo "================================================"
echo "PostgreSQL Backup Script"
echo "================================================"
echo ""
echo "Database: $DB_NAME"
echo "Host: $DB_HOST:$DB_PORT"
echo "Timestamp: $(date)"
echo ""

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Export password for pg_dump
export PGPASSWORD="$DB_PASSWORD"

echo "Starting backup..."

# Perform backup
if command -v docker-compose &> /dev/null && [ -f "$PROJECT_DIR/docker-compose.yml" ]; then
    # Backup from Docker container
    echo "Using Docker container for backup..."
    docker-compose exec -T db pg_dump -U "$DB_USER" -d "$DB_NAME" > "$BACKUP_FILE"
else
    # Backup from local PostgreSQL
    echo "Using local PostgreSQL for backup..."
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" > "$BACKUP_FILE"
fi

# Check if backup was successful
if [ $? -eq 0 ] && [ -f "$BACKUP_FILE" ]; then
    echo "✓ Backup created successfully"
    
    # Get file size
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "Backup size: $BACKUP_SIZE"
    
    # Compress backup
    echo "Compressing backup..."
    gzip "$BACKUP_FILE"
    
    if [ -f "$BACKUP_COMPRESSED" ]; then
        COMPRESSED_SIZE=$(du -h "$BACKUP_COMPRESSED" | cut -f1)
        echo "✓ Backup compressed successfully"
        echo "Compressed size: $COMPRESSED_SIZE"
        echo "Backup location: $BACKUP_COMPRESSED"
    else
        echo "✗ Failed to compress backup"
        exit 1
    fi
else
    echo "✗ Backup failed"
    exit 1
fi

# Clean up old backups
echo ""
echo "Cleaning up old backups (older than $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "postgres_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete
REMAINING=$(find "$BACKUP_DIR" -name "postgres_*.sql.gz" -type f | wc -l)
echo "✓ Cleanup completed. $REMAINING backup(s) remaining"

# Upload to S3/MinIO (optional)
if [ ! -z "$BACKUP_S3_BUCKET" ] && command -v aws &> /dev/null; then
    echo ""
    echo "Uploading to S3/MinIO..."
    
    AWS_ENDPOINT=${MINIO_ENDPOINT:-}
    S3_PATH="s3://$BACKUP_S3_BUCKET/postgres/$TIMESTAMP.sql.gz"
    
    if [ ! -z "$AWS_ENDPOINT" ]; then
        aws --endpoint-url "$AWS_ENDPOINT" s3 cp "$BACKUP_COMPRESSED" "$S3_PATH"
    else
        aws s3 cp "$BACKUP_COMPRESSED" "$S3_PATH"
    fi
    
    if [ $? -eq 0 ]; then
        echo "✓ Backup uploaded to S3: $S3_PATH"
    else
        echo "⚠ Failed to upload backup to S3"
    fi
fi

# Create backup log
LOG_FILE="$PROJECT_DIR/writable/logs/backup.log"
echo "$(date) - PostgreSQL backup completed: $BACKUP_COMPRESSED" >> "$LOG_FILE"

echo ""
echo "================================================"
echo "Backup completed successfully!"
echo "================================================"

# Unset password
unset PGPASSWORD

exit 0
