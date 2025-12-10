#!/bin/bash

# MinIO Backup Script for GeoSPT Manager
# Usage: ./scripts/backup-minio.sh
# Backs up MinIO buckets to local storage

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Load environment variables
if [ -f "$PROJECT_DIR/.env" ]; then
    export $(cat "$PROJECT_DIR/.env" | grep -v '^#' | xargs)
fi

# Configuration
MINIO_ENDPOINT=${MINIO_ENDPOINT:-http://localhost:9000}
MINIO_ACCESS_KEY=${MINIO_ACCESS_KEY:-minioadmin}
MINIO_SECRET_KEY=${MINIO_SECRET_KEY:-minioadmin123}

BACKUP_DIR="$PROJECT_DIR/backups/minio"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Buckets to backup
BUCKETS=("geospt-uploads" "geospt-pdfs" "geospt-thumbnails")

# Retention settings (days)
RETENTION_DAYS=30

echo "================================================"
echo "MinIO Backup Script"
echo "================================================"
echo ""
echo "Endpoint: $MINIO_ENDPOINT"
echo "Timestamp: $(date)"
echo ""

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Check if mc (MinIO Client) is available
if ! command -v mc &> /dev/null; then
    echo "✗ MinIO Client (mc) not found"
    echo "Please install: https://docs.min.io/docs/minio-client-quickstart-guide.html"
    exit 1
fi

# Configure MinIO Client
echo "Configuring MinIO Client..."
mc alias set backup "$MINIO_ENDPOINT" "$MINIO_ACCESS_KEY" "$MINIO_SECRET_KEY" --api S3v4
echo ""

# Backup each bucket
TOTAL_SIZE=0

for BUCKET in "${BUCKETS[@]}"; do
    echo "Backing up bucket: $BUCKET"
    
    BUCKET_BACKUP_DIR="$BACKUP_DIR/${BUCKET}_${TIMESTAMP}"
    mkdir -p "$BUCKET_BACKUP_DIR"
    
    # Mirror bucket to local directory
    mc mirror backup/"$BUCKET" "$BUCKET_BACKUP_DIR" --quiet
    
    if [ $? -eq 0 ]; then
        # Compress backup
        COMPRESSED_FILE="$BACKUP_DIR/${BUCKET}_${TIMESTAMP}.tar.gz"
        tar -czf "$COMPRESSED_FILE" -C "$BACKUP_DIR" "$(basename "$BUCKET_BACKUP_DIR")"
        
        # Get size
        BUCKET_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
        echo "✓ Bucket $BUCKET backed up successfully (Size: $BUCKET_SIZE)"
        
        # Remove uncompressed directory
        rm -rf "$BUCKET_BACKUP_DIR"
        
        # Add to total size (for reporting)
        SIZE_BYTES=$(stat -f%z "$COMPRESSED_FILE" 2>/dev/null || stat -c%s "$COMPRESSED_FILE" 2>/dev/null || echo 0)
        TOTAL_SIZE=$((TOTAL_SIZE + SIZE_BYTES))
    else
        echo "✗ Failed to backup bucket: $BUCKET"
    fi
    
    echo ""
done

# Format total size
TOTAL_SIZE_MB=$((TOTAL_SIZE / 1024 / 1024))
echo "Total backup size: ${TOTAL_SIZE_MB}MB"
echo ""

# Clean up old backups
echo "Cleaning up old backups (older than $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "*.tar.gz" -type f -mtime +$RETENTION_DAYS -delete
REMAINING=$(find "$BACKUP_DIR" -name "*.tar.gz" -type f | wc -l)
echo "✓ Cleanup completed. $REMAINING backup(s) remaining"
echo ""

# Create backup log
LOG_FILE="$PROJECT_DIR/writable/logs/backup.log"
echo "$(date) - MinIO backup completed. Size: ${TOTAL_SIZE_MB}MB" >> "$LOG_FILE"

echo "================================================"
echo "Backup completed successfully!"
echo "================================================"
echo ""
echo "Backup location: $BACKUP_DIR"

exit 0
