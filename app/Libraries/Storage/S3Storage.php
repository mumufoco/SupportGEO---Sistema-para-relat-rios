<?php

namespace App\Libraries\Storage;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Config\Storage as StorageConfig;

class S3Storage
{
    protected S3Client $client;
    protected StorageConfig $config;
    protected array $diskConfig;

    public function __construct(string $disk = 's3')
    {
        $this->config = config('Storage');
        $this->diskConfig = $this->config->disks[$disk];
        
        $clientConfig = [
            'version' => $this->diskConfig['version'],
            'region'  => $this->diskConfig['region'],
            'credentials' => [
                'key'    => $this->diskConfig['key'],
                'secret' => $this->diskConfig['secret'],
            ],
        ];
        
        // MinIO-specific configuration
        if (isset($this->diskConfig['endpoint'])) {
            $clientConfig['endpoint'] = $this->diskConfig['endpoint'];
        }
        
        if (isset($this->diskConfig['use_path_style_endpoint'])) {
            $clientConfig['use_path_style_endpoint'] = $this->diskConfig['use_path_style_endpoint'];
        }
        
        $this->client = new S3Client($clientConfig);
    }

    /**
     * Upload a file to S3/MinIO
     */
    public function put(string $path, $contents, array $options = []): bool
    {
        try {
            $bucket = $options['bucket'] ?? $this->diskConfig['bucket'];
            
            $params = [
                'Bucket' => $bucket,
                'Key'    => $path,
                'Body'   => $contents,
            ];
            
            if (isset($options['ContentType'])) {
                $params['ContentType'] = $options['ContentType'];
            }
            
            if (isset($options['ACL'])) {
                $params['ACL'] = $options['ACL'];
            }
            
            if (isset($options['Metadata'])) {
                $params['Metadata'] = $options['Metadata'];
            }
            
            $this->client->putObject($params);
            
            return true;
        } catch (AwsException $e) {
            log_message('error', 'S3 upload error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload a file from local path
     */
    public function putFile(string $localPath, string $s3Path, array $options = []): bool
    {
        if (!file_exists($localPath)) {
            return false;
        }
        
        $contents = file_get_contents($localPath);
        
        if (!isset($options['ContentType'])) {
            $options['ContentType'] = mime_content_type($localPath);
        }
        
        return $this->put($s3Path, $contents, $options);
    }

    /**
     * Get file contents
     */
    public function get(string $path, ?string $bucket = null): ?string
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $result = $this->client->getObject([
                'Bucket' => $bucket,
                'Key'    => $path,
            ]);
            
            return (string) $result['Body'];
        } catch (AwsException $e) {
            log_message('error', 'S3 get error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download file to local path
     */
    public function download(string $s3Path, string $localPath, ?string $bucket = null): bool
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $this->client->getObject([
                'Bucket' => $bucket,
                'Key'    => $s3Path,
                'SaveAs' => $localPath,
            ]);
            
            return true;
        } catch (AwsException $e) {
            log_message('error', 'S3 download error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if file exists
     */
    public function exists(string $path, ?string $bucket = null): bool
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            return $this->client->doesObjectExist($bucket, $path);
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * Delete a file
     */
    public function delete(string $path, ?string $bucket = null): bool
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $this->client->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $path,
            ]);
            
            return true;
        } catch (AwsException $e) {
            log_message('error', 'S3 delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete multiple files
     */
    public function deleteMultiple(array $paths, ?string $bucket = null): bool
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $objects = array_map(function ($path) {
                return ['Key' => $path];
            }, $paths);
            
            $this->client->deleteObjects([
                'Bucket' => $bucket,
                'Delete' => [
                    'Objects' => $objects,
                ],
            ]);
            
            return true;
        } catch (AwsException $e) {
            log_message('error', 'S3 delete multiple error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a presigned URL for upload
     */
    public function getPresignedUploadUrl(string $path, int $expiry = 3600, ?string $bucket = null): ?string
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $cmd = $this->client->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key'    => $path,
            ]);
            
            $request = $this->client->createPresignedRequest($cmd, "+{$expiry} seconds");
            
            return (string) $request->getUri();
        } catch (AwsException $e) {
            log_message('error', 'S3 presigned upload URL error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a presigned URL for download
     */
    public function getPresignedDownloadUrl(string $path, int $expiry = 3600, ?string $bucket = null): ?string
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $cmd = $this->client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => $path,
            ]);
            
            $request = $this->client->createPresignedRequest($cmd, "+{$expiry} seconds");
            
            return (string) $request->getUri();
        } catch (AwsException $e) {
            log_message('error', 'S3 presigned download URL error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get file metadata
     */
    public function getMetadata(string $path, ?string $bucket = null): ?array
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $result = $this->client->headObject([
                'Bucket' => $bucket,
                'Key'    => $path,
            ]);
            
            return [
                'ContentType' => $result['ContentType'] ?? null,
                'ContentLength' => $result['ContentLength'] ?? null,
                'LastModified' => $result['LastModified'] ?? null,
                'ETag' => $result['ETag'] ?? null,
                'Metadata' => $result['Metadata'] ?? [],
            ];
        } catch (AwsException $e) {
            log_message('error', 'S3 metadata error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * List files in a directory
     */
    public function listFiles(string $prefix = '', ?string $bucket = null): array
    {
        try {
            $bucket = $bucket ?? $this->diskConfig['bucket'];
            
            $result = $this->client->listObjectsV2([
                'Bucket' => $bucket,
                'Prefix' => $prefix,
            ]);
            
            if (!isset($result['Contents'])) {
                return [];
            }
            
            return array_map(function ($object) {
                return [
                    'key' => $object['Key'],
                    'size' => $object['Size'],
                    'last_modified' => $object['LastModified']->format('Y-m-d H:i:s'),
                ];
            }, $result['Contents']);
        } catch (AwsException $e) {
            log_message('error', 'S3 list files error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Copy a file
     */
    public function copy(string $sourcePath, string $destPath, ?string $sourceBucket = null, ?string $destBucket = null): bool
    {
        try {
            $sourceBucket = $sourceBucket ?? $this->diskConfig['bucket'];
            $destBucket = $destBucket ?? $this->diskConfig['bucket'];
            
            $this->client->copyObject([
                'Bucket'     => $destBucket,
                'Key'        => $destPath,
                'CopySource' => "{$sourceBucket}/{$sourcePath}",
            ]);
            
            return true;
        } catch (AwsException $e) {
            log_message('error', 'S3 copy error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Move a file
     */
    public function move(string $sourcePath, string $destPath, ?string $sourceBucket = null, ?string $destBucket = null): bool
    {
        if ($this->copy($sourcePath, $destPath, $sourceBucket, $destBucket)) {
            return $this->delete($sourcePath, $sourceBucket);
        }
        
        return false;
    }

    /**
     * Get public URL for a file
     */
    public function getUrl(string $path, ?string $bucket = null): string
    {
        $bucket = $bucket ?? $this->diskConfig['bucket'];
        $endpoint = $this->diskConfig['endpoint'];
        
        // Remove protocol from endpoint
        $endpoint = preg_replace('#^https?://#', '', $endpoint);
        
        $useSSL = $this->diskConfig['use_ssl'] ?? false;
        $protocol = $useSSL ? 'https' : 'http';
        
        if ($this->diskConfig['use_path_style_endpoint'] ?? false) {
            return "{$protocol}://{$endpoint}/{$bucket}/{$path}";
        }
        
        return "{$protocol}://{$bucket}.{$endpoint}/{$path}";
    }
}
