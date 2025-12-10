<?php

namespace App\Workers;

use App\Services\ImportService;
use App\Libraries\Storage\S3Storage;

class ImportWorker extends BaseWorker
{
    protected ImportService $importService;
    protected S3Storage $storage;

    public function __construct()
    {
        parent::__construct('data-import');
        
        $this->importService = new ImportService();
        $this->storage = new S3Storage();
    }

    /**
     * Process import job
     */
    protected function process(array $job): bool
    {
        $filepath = $this->getJobData($job, 'filepath');
        $obraId = $this->getJobData($job, 'obra_id');
        $usuarioId = $this->getJobData($job, 'usuario_id');
        $s3Key = $this->getJobData($job, 's3_key');
        
        if (!$filepath && !$s3Key) {
            log_message('error', 'Import job missing filepath or s3_key');
            return false;
        }
        
        if (!$obraId || !$usuarioId) {
            log_message('error', 'Import job missing obra_id or usuario_id');
            return false;
        }
        
        try {
            // Download file from S3 if needed
            if ($s3Key) {
                $tempPath = WRITEPATH . 'temp/' . uniqid() . '.xlsx';
                
                if (!$this->storage->download($s3Key, $tempPath)) {
                    log_message('error', "Failed to download import file from S3: {$s3Key}");
                    return false;
                }
                
                $filepath = $tempPath;
            }
            
            if (!file_exists($filepath)) {
                log_message('error', "Import file not found: {$filepath}");
                return false;
            }
            
            // Process import
            $resultados = $this->importService->importarExcel($filepath, $obraId, $usuarioId);
            
            // Clean up temp file if downloaded from S3
            if ($s3Key && isset($tempPath)) {
                @unlink($tempPath);
            }
            
            // Log results
            log_message('info', "Import completed: " . json_encode([
                'sucesso' => $resultados['sucesso'],
                'sondagens' => $resultados['sondagens_criadas'],
                'amostras' => $resultados['amostras_criadas'],
                'camadas' => $resultados['camadas_criadas'],
                'erros' => count($resultados['erros']),
            ]));
            
            return $resultados['sucesso'];
        } catch (\Throwable $e) {
            log_message('error', "Import processing error: {$e->getMessage()}\n{$e->getTraceAsString()}");
            
            // Clean up temp file if it exists
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            
            return false;
        }
    }
}
