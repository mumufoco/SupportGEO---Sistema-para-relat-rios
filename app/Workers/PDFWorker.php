<?php

namespace App\Workers;

use App\Libraries\SondagemPDFGenerator;
use App\Libraries\NBR\NBRReportHelper;
use App\Libraries\Storage\S3Storage;
use App\Repositories\SondagemRepository;
use App\Models\SondagemModel;
use App\Models\EmpresaModel;
use App\Models\ResponsavelTecnicoModel;
use App\Models\ObraModel;
use App\Models\ProjetoModel;
use Config\Storage as StorageConfig;

class PDFWorker extends BaseWorker
{
    protected SondagemRepository $sondagemRepo;
    protected NBRReportHelper $reportHelper;
    protected S3Storage $storage;
    protected StorageConfig $storageConfig;

    public function __construct()
    {
        parent::__construct('pdf-generation');
        
        $this->sondagemRepo = new SondagemRepository();
        $this->reportHelper = new NBRReportHelper();
        $this->storage = new S3Storage();
        $this->storageConfig = config('Storage');
    }

    /**
     * Process PDF generation job
     */
    protected function process(array $job): bool
    {
        $sondagemId = $this->getJobData($job, 'sondagem_id');
        
        if (!$sondagemId) {
            log_message('error', 'PDF job missing sondagem_id');
            return false;
        }
        
        try {
            // Get sondagem data
            $sondagem = $this->sondagemRepo->findWithRelations($sondagemId);
            
            if (!$sondagem) {
                log_message('error', "Sondagem {$sondagemId} not found");
                return false;
            }
            
            // Get related data
            $obraModel = new ObraModel();
            $obra = $obraModel->findWithProjeto($sondagem['obra_id']);
            
            if (!$obra) {
                log_message('error', "Obra {$sondagem['obra_id']} not found");
                return false;
            }
            
            $projetoModel = new ProjetoModel();
            $projeto = $projetoModel->find($obra['projeto_id']);
            
            $empresaModel = new EmpresaModel();
            $empresa = $empresaModel->find($projeto['empresa_id']);
            
            $responsavelModel = new ResponsavelTecnicoModel();
            $responsavel = $responsavelModel->find($sondagem['responsavel_tecnico_id']);
            
            // Prepare data
            $dadosCompletos = $this->reportHelper->montarDadosCompletos(
                $sondagem,
                $sondagem['amostras'] ?? [],
                $sondagem['camadas'] ?? [],
                $sondagem['fotos'] ?? [],
                $empresa,
                $responsavel,
                $obra,
                $projeto
            );
            
            // Generate PDF
            $pdfGenerator = new SondagemPDFGenerator();
            $localPath = $pdfGenerator->gerar($dadosCompletos);
            
            if (!file_exists($localPath)) {
                log_message('error', "PDF generation failed for sondagem {$sondagemId}");
                return false;
            }
            
            // Upload to S3/MinIO
            $fileName = basename($localPath);
            $s3Path = "pdfs/{$sondagemId}/{$fileName}";
            $bucket = $this->storageConfig->buckets['pdfs'];
            
            $uploaded = $this->storage->putFile($localPath, $s3Path, [
                'bucket' => $bucket,
                'ContentType' => 'application/pdf',
                'Metadata' => [
                    'sondagem_id' => (string) $sondagemId,
                    'generated_at' => date('Y-m-d H:i:s'),
                ],
            ]);
            
            if (!$uploaded) {
                log_message('error', "Failed to upload PDF to S3 for sondagem {$sondagemId}");
                @unlink($localPath);
                return false;
            }
            
            // Update sondagem with PDF path
            $sondagemModel = new SondagemModel();
            $sondagemModel->update($sondagemId, [
                'pdf_path' => $s3Path,
            ]);
            
            // Clean up local file
            @unlink($localPath);
            
            log_message('info', "PDF generated and uploaded successfully for sondagem {$sondagemId}");
            
            return true;
        } catch (\Throwable $e) {
            log_message('error', "PDF generation error: {$e->getMessage()}\n{$e->getTraceAsString()}");
            return false;
        }
    }
}
