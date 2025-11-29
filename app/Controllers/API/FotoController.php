<?php

namespace App\Controllers\API;

use App\Models\FotoModel;
use App\Models\SondagemModel;
use App\Services\ExifService;
use App\Repositories\FotoRepository;

class FotoController extends BaseAPIController
{
    private FotoModel $model;
    private FotoRepository $repository;
    private ExifService $exifService;

    public function __construct()
    {
        $this->model = new FotoModel();
        $this->repository = new FotoRepository();
        $this->exifService = new ExifService();
    }

    public function index($sondagemId = null)
    {
        try {
            if (!$sondagemId) {
                return $this->respondError('ID da sondagem não informado', 400);
            }

            $fotos = $this->repository->findBySondagem($sondagemId);
            return $this->respondSuccess($fotos);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar fotos: ' . $e->getMessage(), 500);
        }
    }

    public function upload($sondagemId = null)
    {
        try {
            if (!$sondagemId) {
                return $this->respondError('ID da sondagem não informado', 400);
            }

            $sondagemModel = new SondagemModel();
            $sondagem = $sondagemModel->find($sondagemId);

            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $files = $this->request->getFiles();

            if (empty($files['fotos'])) {
                return $this->respondError('Nenhuma foto enviada', 400);
            }

            $uploadPath = WRITEPATH . 'uploads/fotos/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $tipoFoto = $this->request->getPost('tipo_foto') ?? 'ensaio_spt';
            $descricao = $this->request->getPost('descricao') ?? '';

            $fotosEnviadas = [];
            $erros = [];

            foreach ($files['fotos'] as $foto) {
                if (!$foto->isValid()) {
                    $erros[] = $foto->getName() . ': ' . $foto->getErrorString();
                    continue;
                }

                $extensao = strtolower($foto->getExtension());
                if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $erros[] = $foto->getName() . ': Tipo de arquivo não permitido';
                    continue;
                }

                if ($foto->getSizeByUnit('mb') > 10) {
                    $erros[] = $foto->getName() . ': Arquivo muito grande (max 10MB)';
                    continue;
                }

                $nomeOriginal = $foto->getName();
                $novoNome = uniqid() . '_' . time() . '.' . $extensao;

                $foto->move($uploadPath, $novoNome);
                $caminhoCompleto = $uploadPath . $novoNome;

                $metadados = $this->exifService->extrairMetadados($caminhoCompleto);
                $dimensoes = $this->exifService->obterDimensoesImagem($caminhoCompleto);

                $ordemAtual = $this->model->where('sondagem_id', $sondagemId)->countAllResults();

                $dadosFoto = [
                    'sondagem_id' => $sondagemId,
                    'arquivo' => $novoNome,
                    'nome_original' => $nomeOriginal,
                    'tipo_foto' => $tipoFoto,
                    'descricao' => $descricao,
                    'latitude' => $metadados['latitude'],
                    'longitude' => $metadados['longitude'],
                    'altitude' => $metadados['altitude'],
                    'velocidade' => $metadados['velocidade'],
                    'data_hora_exif' => $metadados['data_hora'],
                    'coordenada_este' => $metadados['coordenada_este'],
                    'coordenada_norte' => $metadados['coordenada_norte'],
                    'zona_utm' => $metadados['zona_utm'],
                    'fabricante' => $metadados['fabricante'],
                    'modelo' => $metadados['modelo'],
                    'orientacao' => $metadados['orientacao'],
                    'largura' => $dimensoes['largura'],
                    'altura' => $dimensoes['altura'],
                    'tamanho_bytes' => filesize($caminhoCompleto),
                    'mime_type' => $dimensoes['mime'],
                    'ordem' => $ordemAtual + 1
                ];

                $usuarioId = $this->getUsuarioId();
                $fotoSalva = $this->repository->create($dadosFoto, $usuarioId);

                if ($fotoSalva) {
                    $fotosEnviadas[] = $fotoSalva;
                } else {
                    $erros[] = $nomeOriginal . ': Erro ao salvar no banco';
                    @unlink($caminhoCompleto);
                }
            }

            $mensagem = count($fotosEnviadas) . ' foto(s) enviada(s) com sucesso';

            return $this->respondCreated([
                'fotos' => $fotosEnviadas,
                'erros' => $erros,
                'total_enviadas' => count($fotosEnviadas),
                'total_erros' => count($erros)
            ], $mensagem);

        } catch (\Exception $e) {
            return $this->respondError('Erro ao fazer upload: ' . $e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $foto = $this->repository->find($id);

            if (!$foto) {
                return $this->respondNotFound('Foto não encontrada');
            }

            return $this->respondSuccess($foto);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar foto: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $foto = $this->repository->find($id);

            if (!$foto) {
                return $this->respondNotFound('Foto não encontrada');
            }

            $filepath = WRITEPATH . 'uploads/fotos/' . $foto['arquivo'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $usuarioId = $this->getUsuarioId();
            $this->repository->delete($id, $usuarioId);

            return $this->respondSuccess(null, 'Foto excluída com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao excluir foto: ' . $e->getMessage(), 500);
        }
    }

    public function reordenar($sondagemId = null)
    {
        try {
            if (!$sondagemId) {
                return $this->respondError('ID da sondagem não informado', 400);
            }

            $data = $this->request->getJSON(true);
            $ordem = $data['ordem'] ?? [];

            if (empty($ordem)) {
                return $this->respondError('Lista de ordenação não informada', 400);
            }

            foreach ($ordem as $index => $fotoId) {
                $this->model->update($fotoId, ['ordem' => $index + 1]);
            }

            return $this->respondSuccess(null, 'Fotos reordenadas com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao reordenar fotos: ' . $e->getMessage(), 500);
        }
    }

    public function atualizar($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $foto = $this->repository->find($id);
            if (!$foto) {
                return $this->respondNotFound('Foto não encontrada');
            }

            $data = $this->request->getJSON(true);
            $usuarioId = $this->getUsuarioId();

            $fotoAtualizada = $this->repository->update($id, $data, $usuarioId);

            return $this->respondSuccess($fotoAtualizada, 'Foto atualizada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao atualizar foto: ' . $e->getMessage(), 500);
        }
    }
}
