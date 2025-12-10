# MinIO Configuration for GeoSPT Manager

## Overview

MinIO provides S3-compatible object storage for the GeoSPT Manager application. It stores:
- Uploaded photos from surveys
- Generated PDF reports
- Thumbnail images
- Import/export files

## Access

- **MinIO API**: http://localhost:9000
- **MinIO Console**: http://localhost:9001
- **Default Credentials**: 
  - Username: `minioadmin`
  - Password: `minioadmin123`

## Buckets

The following buckets are automatically created on startup:

1. **geospt-uploads**: Original uploaded files (photos, documents)
2. **geospt-pdfs**: Generated PDF reports
3. **geospt-thumbnails**: Optimized and thumbnail images

## Configuration

Environment variables for MinIO configuration:

```bash
MINIO_ENDPOINT=http://minio:9000  # Internal Docker network
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin123
MINIO_BUCKET=geospt-uploads
MINIO_REGION=us-east-1
MINIO_USE_SSL=false
```

## Production Notes

For production deployments:

1. **Change default credentials** in docker-compose.yml
2. **Enable SSL/TLS** by setting MINIO_USE_SSL=true
3. **Configure persistent storage** volumes
4. **Set up backup strategy** for MinIO data
5. **Configure bucket policies** for appropriate access control

## Bucket Policies

Buckets are configured with download-only public access for development. In production:

```bash
# Set private access (recommended for production)
mc anonymous set none myminio/geospt-uploads

# Or use presigned URLs for controlled access
```

## Testing Connection

```bash
# Using MinIO Client (mc)
mc alias set myminio http://localhost:9000 minioadmin minioadmin123
mc ls myminio

# List files in bucket
mc ls myminio/geospt-uploads
```

## Backup and Restore

```bash
# Backup bucket
mc mirror myminio/geospt-uploads /backup/geospt-uploads

# Restore bucket
mc mirror /backup/geospt-uploads myminio/geospt-uploads
```

## Performance Tuning

For high-traffic scenarios:

1. Increase MINIO_STORAGE_CLASS_STANDARD replicas
2. Configure distributed mode with multiple MinIO servers
3. Use a reverse proxy (nginx) for load balancing
4. Enable caching at the application level

## Monitoring

Access MinIO console at http://localhost:9001 to monitor:
- Storage usage
- API requests
- Bandwidth utilization
- Bucket statistics
