<?php

namespace App\Controllers\API;

use App\Services\ImportService;

class ImportController extends BaseAPIController
{
    private ImportService $importService;

    public function __construct()
    {
        $this->importService = new ImportService();
    }

    public function excel()
    {
        try {
            $arquivo = $this->request->getFile('arquivo');
            $obraId = $this->request->getPost('obra_id');

            if (!$arquivo || !$arquivo->isValid()) {
                return $this->respondError('Arquivo não enviado ou inválido', 400);
            }

            if (!$obraId) {
                return $this->respondError('ID da obra é obrigatório', 400);
            }

            $extensao = strtolower($arquivo->getExtension());
            if (!in_array($extensao, ['xlsx', 'xls', 'csv'])) {
                return $this->respondError('Formato de arquivo não suportado. Use xlsx, xls ou csv.', 400);
            }

            $uploadPath = WRITEPATH . 'uploads/imports/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $novoNome = uniqid() . '_' . time() . '.' . $extensao;
            $arquivo->move($uploadPath, $novoNome);
            $filepath = $uploadPath . $novoNome;

            $usuarioId = $this->getUsuarioId() ?? 1;
            $resultado = $this->importService->importarExcel($filepath, $obraId, $usuarioId);

            @unlink($filepath);

            $mensagem = sprintf(
                'Importação concluída: %d sondagens, %d amostras, %d camadas',
                $resultado['sondagens_criadas'],
                $resultado['amostras_criadas'],
                $resultado['camadas_criadas']
            );

            return $this->respondSuccess($resultado, $mensagem);

        } catch (\Exception $e) {
            return $this->respondError('Erro ao importar: ' . $e->getMessage(), 500);
        }
    }

    public function template()
    {
        try {
            $filepath = $this->importService->gerarTemplate();

            if (!file_exists($filepath)) {
                return $this->respondError('Erro ao gerar template', 500);
            }

            return $this->response
                ->download($filepath, null)
                ->setFileName('template_importacao_sondagens.xlsx');

        } catch (\Exception $e) {
            return $this->respondError('Erro ao gerar template: ' . $e->getMessage(), 500);
        }
    }
}
