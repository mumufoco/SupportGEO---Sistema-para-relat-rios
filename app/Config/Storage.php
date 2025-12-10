<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Storage Configuration (MinIO / S3)
 */
class Storage extends BaseConfig
{
    /**
     * Default storage disk
     */
    public string $default = 's3';

    /**
     * Storage disks configuration
     */
    public array $disks = [
        's3' => [
            'driver'     => 's3',
            'endpoint'   => 'http://minio:9000',
            'key'        => 'minioadmin',
            'secret'     => 'minioadmin123',
            'region'     => 'us-east-1',
            'bucket'     => 'geospt-uploads',
            'use_ssl'    => false,
            'use_path_style_endpoint' => true,
            'version'    => 'latest',
        ],

        'local' => [
            'driver' => 'local',
            'root'   => WRITEPATH . 'uploads',
        ],
    ];

    /**
     * Buckets configuration
     */
    public array $buckets = [
        'uploads'    => 'geospt-uploads',
        'pdfs'       => 'geospt-pdfs',
        'thumbnails' => 'geospt-thumbnails',
    ];

    /**
     * Upload configuration
     */
    public array $upload = [
        'max_size'        => 104857600, // 100MB in bytes
        'allowed_types'   => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xlsx', 'xls', 'csv'],
        'image_types'     => ['jpg', 'jpeg', 'png', 'gif'],
        'document_types'  => ['pdf', 'xlsx', 'xls', 'csv'],
    ];

    /**
     * Thumbnail configuration
     */
    public array $thumbnail = [
        'width'   => 300,
        'height'  => 300,
        'quality' => 80,
        'crop'    => false,
    ];

    /**
     * Presigned URL configuration (in seconds)
     */
    public array $presigned = [
        'upload_expiry'   => 3600,   // 1 hour
        'download_expiry' => 86400,  // 24 hours
    ];

    public function __construct()
    {
        parent::__construct();

        // Override with environment variables
        if ($endpoint = env('MINIO_ENDPOINT')) {
            $this->disks['s3']['endpoint'] = $endpoint;
        }

        if ($key = env('MINIO_ACCESS_KEY')) {
            $this->disks['s3']['key'] = $key;
        }

        if ($secret = env('MINIO_SECRET_KEY')) {
            $this->disks['s3']['secret'] = $secret;
        }

        if ($bucket = env('MINIO_BUCKET')) {
            $this->disks['s3']['bucket'] = $bucket;
        }

        if ($region = env('MINIO_REGION')) {
            $this->disks['s3']['region'] = $region;
        }

        if (isset($_ENV['MINIO_USE_SSL'])) {
            $this->disks['s3']['use_ssl'] = filter_var(env('MINIO_USE_SSL'), FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($_ENV['MINIO_USE_PATH_STYLE'])) {
            $this->disks['s3']['use_path_style_endpoint'] = filter_var(env('MINIO_USE_PATH_STYLE'), FILTER_VALIDATE_BOOLEAN);
        }

        // Update bucket names from environment
        if ($uploadBucket = env('MINIO_BUCKET')) {
            $this->buckets['uploads'] = $uploadBucket;
        }

        if ($pdfBucket = env('MINIO_BUCKET_PDF')) {
            $this->buckets['pdfs'] = $pdfBucket;
        }

        if ($thumbnailBucket = env('MINIO_BUCKET_THUMBNAILS')) {
            $this->buckets['thumbnails'] = $thumbnailBucket;
        }
    }
}
