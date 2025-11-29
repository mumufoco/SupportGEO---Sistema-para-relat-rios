<?php

namespace App\Controllers\API;

use App\Libraries\SondagemPDFGenerator;
use App\Libraries\NBR\NBRReportHelper;
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

    public function __construct()
    {
        $this->sondagemRepo = new SondagemRepository();
        $this->reportHelper = new NBRReportHelper();
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
}
