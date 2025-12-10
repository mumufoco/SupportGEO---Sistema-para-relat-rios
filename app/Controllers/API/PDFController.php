<?php

namespace App\Controllers\API;

use App\Libraries\SondagemPDFGenerator;
use App\Libraries\NBR\NBRReportHelper;
use App\Libraries\Queue\RedisQueue;
use App\Libraries\Storage\S3Storage;
use App\Models\SondagemModel;
use App\Models\EmpresaModel;
use App\Models\ResponsavelTecnicoModel;
use App\Models\ObraModel;
use App\Models\ProjetoModel;
use App\Repositories\SondagemRepository;

class PDFController extends BaseAPIController
{
    private SondagemRepository $sondagemRepo;
    private NBRReportHelper $reportHelper;
    private RedisQueue $queue;
    private S3Storage $storage;

    public function __construct()
    {
        $this->sondagemRepo = new SondagemRepository();
        $this->reportHelper = new NBRReportHelper();
        $this->queue = new RedisQueue();
        $this->storage = new S3Storage();
    }

    public function gerarSondagem($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID da sondagem não informado', 400);
            }

            $sondagem = $this->sondagemRepo->findWithRelations($id);
            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $obraModel = new ObraModel();
            $obra = $obraModel->findWithProjeto($sondagem['obra_id']);

            if (!$obra) {
                return $this->respondError('Obra não encontrada', 400);
            }

            $projetoModel = new ProjetoModel();
            $projeto = $projetoModel->find($obra['projeto_id']);

            if (!$projeto) {
                return $this->respondError('Projeto não encontrado', 400);
            }

            $empresaModel = new EmpresaModel();
            $empresa = $empresaModel->find($projeto['empresa_id']);

            if (!$empresa) {
                return $this->respondError('Empresa não encontrada', 400);
            }

            $responsavelModel = new ResponsavelTecnicoModel();
            $responsavel = $responsavelModel->find($sondagem['responsavel_tecnico_id']);

            if (!$responsavel) {
                return $this->respondError('Responsável técnico não encontrado', 400);
            }

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

            $pdfGenerator = new SondagemPDFGenerator();
            $filePath = $pdfGenerator->gerar($dadosCompletos);

            if (!file_exists($filePath)) {
                return $this->respondError('Erro ao gerar PDF', 500);
            }

            $fileName = basename($filePath);

            return $this->respondSuccess([
                'file_path' => $filePath,
                'file_name' => $fileName,
                'download_url' => base_url('api/pdf/download/' . $fileName)
            ], 'PDF gerado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao gerar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function download($fileName = null)
    {
        try {
            if (!$fileName) {
                return $this->respondError('Nome do arquivo não informado', 400);
            }

            $filePath = WRITEPATH . 'uploads/pdfs/' . $fileName;

            if (!file_exists($filePath)) {
                return $this->respondNotFound('Arquivo não encontrado');
            }

            return $this->response->download($filePath, null)->setFileName($fileName);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao fazer download: ' . $e->getMessage(), 500);
        }
    }

    public function preview($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID da sondagem não informado', 400);
            }

            $response = $this->gerarSondagem($id);
            $data = json_decode($response->getBody(), true);

            if ($data['status'] !== 'success') {
                return $response;
            }

            $filePath = $data['data']['file_path'];

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setBody(file_get_contents($filePath));
        } catch (\Exception $e) {
            return $this->respondError('Erro ao visualizar PDF: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enqueue PDF generation job (async)
     */
    public function enqueue($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID da sondagem não informado', 400);
            }

            $sondagem = $this->sondagemRepo->find($id);
            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $usuarioId = $this->getUsuarioId();

            // Create job record in database
            $db = \Config\Database::connect();
            $jobUuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
            
            $payload = [
                'sondagem_id' => $id,
                'usuario_id' => $usuarioId,
            ];

            $db->table('jobs')->insert([
                'uuid' => $jobUuid,
                'queue' => 'pdf-generation',
                'type' => 'pdf_generation',
                'status' => 'pending',
                'payload' => json_encode($payload),
                'attempts' => 0,
                'max_attempts' => 3,
                'available_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Enqueue job to Redis
            $this->queue->push('pdf-generation', [
                'type' => 'pdf',
                'data' => $payload,
                'job_uuid' => $jobUuid,
            ]);

            return $this->respondSuccess([
                'job_uuid' => $jobUuid,
                'status_url' => base_url("api/jobs/{$jobUuid}"),
                'message' => 'PDF generation job enqueued',
            ], 'Job enfileirado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao enfileirar geração de PDF: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get PDF download URL if available
     */
    public function downloadUrl($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID da sondagem não informado', 400);
            }

            $sondagemModel = new SondagemModel();
            $sondagem = $sondagemModel->find($id);

            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            if (empty($sondagem['pdf_path'])) {
                return $this->respondError('PDF ainda não foi gerado para esta sondagem', 404);
            }

            // Check if file exists in S3
            if (!$this->storage->exists($sondagem['pdf_path'])) {
                return $this->respondError('Arquivo PDF não encontrado no storage', 404);
            }

            // Generate presigned download URL
            $storageConfig = config('Storage');
            $downloadUrl = $this->storage->getPresignedDownloadUrl(
                $sondagem['pdf_path'],
                $storageConfig->presigned['download_expiry']
            );

            if (!$downloadUrl) {
                return $this->respondError('Erro ao gerar URL de download', 500);
            }

            return $this->respondSuccess([
                'download_url' => $downloadUrl,
                'expires_in' => $storageConfig->presigned['download_expiry'],
                'file_name' => basename($sondagem['pdf_path']),
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao obter URL de download: ' . $e->getMessage(), 500);
        }
    }
}
