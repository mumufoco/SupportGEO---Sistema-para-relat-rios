<?php

namespace App\Workers;

use App\Libraries\Storage\S3Storage;
use App\Models\FotoModel;
use Intervention\Image\ImageManagerStatic as Image;
use Config\Storage as StorageConfig;

class ImageWorker extends BaseWorker
{
    protected S3Storage $storage;
    protected StorageConfig $storageConfig;

    public function __construct()
    {
        parent::__construct('image-processing');
        
        $this->storage = new S3Storage();
        $this->storageConfig = config('Storage');
        
        // Configure Intervention Image
        Image::configure(['driver' => 'gd']);
    }

    /**
     * Process image optimization and thumbnail generation
     */
    protected function process(array $job): bool
    {
        $fotoId = $this->getJobData($job, 'foto_id');
        $action = $this->getJobData($job, 'action', 'thumbnail');
        
        if (!$fotoId) {
            log_message('error', 'Image job missing foto_id');
            return false;
        }
        
        try {
            $fotoModel = new FotoModel();
            $foto = $fotoModel->find($fotoId);
            
            if (!$foto) {
                log_message('error', "Foto {$fotoId} not found");
                return false;
            }
            
            if ($action === 'thumbnail') {
                return $this->generateThumbnail($foto);
            } elseif ($action === 'optimize') {
                return $this->optimizeImage($foto);
            }
            
            return false;
        } catch (\Throwable $e) {
            log_message('error', "Image processing error: {$e->getMessage()}\n{$e->getTraceAsString()}");
            return false;
        }
    }

    /**
     * Generate thumbnail for an image
     */
    protected function generateThumbnail(array $foto): bool
    {
        try {
            // Download original from S3
            $localPath = WRITEPATH . 'temp/' . uniqid() . '_original.jpg';
            $s3Key = $foto['s3_key'] ?? "fotos/{$foto['sondagem_id']}/{$foto['arquivo']}";
            
            if (!$this->storage->download($s3Key, $localPath)) {
                log_message('error', "Failed to download image from S3: {$s3Key}");
                return false;
            }
            
            // Generate thumbnail
            $thumbnailConfig = $this->storageConfig->thumbnail;
            $img = Image::make($localPath);
            
            if ($thumbnailConfig['crop']) {
                $img->fit($thumbnailConfig['width'], $thumbnailConfig['height']);
            } else {
                $img->resize($thumbnailConfig['width'], $thumbnailConfig['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Save thumbnail locally
            $thumbnailPath = WRITEPATH . 'temp/' . uniqid() . '_thumbnail.jpg';
            $img->save($thumbnailPath, $thumbnailConfig['quality']);
            
            // Upload thumbnail to S3
            $thumbnailS3Key = "thumbnails/{$foto['sondagem_id']}/{$foto['id']}_thumb.jpg";
            $bucket = $this->storageConfig->buckets['thumbnails'];
            
            $uploaded = $this->storage->putFile($thumbnailPath, $thumbnailS3Key, [
                'bucket' => $bucket,
                'ContentType' => 'image/jpeg',
                'Metadata' => [
                    'foto_id' => (string) $foto['id'],
                    'original_width' => (string) $foto['largura'],
                    'original_height' => (string) $foto['altura'],
                ],
            ]);
            
            if (!$uploaded) {
                log_message('error', "Failed to upload thumbnail to S3");
                @unlink($localPath);
                @unlink($thumbnailPath);
                return false;
            }
            
            // Update foto record with thumbnail path
            $fotoModel = new FotoModel();
            $fotoModel->update($foto['id'], [
                'thumbnail_path' => $thumbnailS3Key,
            ]);
            
            // Clean up
            @unlink($localPath);
            @unlink($thumbnailPath);
            
            log_message('info', "Thumbnail generated for foto {$foto['id']}");
            
            return true;
        } catch (\Throwable $e) {
            log_message('error', "Thumbnail generation error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Optimize image file size
     */
    protected function optimizeImage(array $foto): bool
    {
        try {
            // Download original from S3
            $localPath = WRITEPATH . 'temp/' . uniqid() . '_optimize.jpg';
            $s3Key = $foto['s3_key'] ?? "fotos/{$foto['sondagem_id']}/{$foto['arquivo']}";
            
            if (!$this->storage->download($s3Key, $localPath)) {
                log_message('error', "Failed to download image from S3: {$s3Key}");
                return false;
            }
            
            // Optimize image
            $img = Image::make($localPath);
            
            // Resize if too large
            if ($img->width() > 2048 || $img->height() > 2048) {
                $img->resize(2048, 2048, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Save optimized
            $optimizedPath = WRITEPATH . 'temp/' . uniqid() . '_optimized.jpg';
            $img->save($optimizedPath, 85);
            
            $originalSize = filesize($localPath);
            $optimizedSize = filesize($optimizedPath);
            
            // Only update if optimization reduced file size
            if ($optimizedSize < $originalSize) {
                $uploaded = $this->storage->putFile($optimizedPath, $s3Key, [
                    'ContentType' => 'image/jpeg',
                ]);
                
                if ($uploaded) {
                    $fotoModel = new FotoModel();
                    $fotoModel->update($foto['id'], [
                        'tamanho_bytes' => $optimizedSize,
                    ]);
                    
                    log_message('info', "Image optimized for foto {$foto['id']}: {$originalSize} -> {$optimizedSize} bytes");
                }
            }
            
            // Clean up
            @unlink($localPath);
            @unlink($optimizedPath);
            
            return true;
        } catch (\Throwable $e) {
            log_message('error', "Image optimization error: {$e->getMessage()}");
            return false;
        }
    }
}
