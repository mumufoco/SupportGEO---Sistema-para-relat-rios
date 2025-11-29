<?php

namespace App\Controllers\API;

use App\Models\ObraModel;
use App\Repositories\BaseRepository;

class ObraController extends BaseAPIController
{
    private ObraModel $model;
    private BaseRepository $repository;

    public function __construct()
    {
        $this->model = new ObraModel();
        $this->repository = new BaseRepository($this->model);
    }

    public function index()
    {
        try {
            $projetoId = $this->request->getGet('projeto_id');

            if ($projetoId) {
                $obras = $this->model->findByProjeto($projetoId);
            } else {
                $params = $this->getPaginationParams();
                $obras = $this->model->findAll($params['limit'], $params['offset']);
            }

            return $this->respondSuccess($obras);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar obras: ' . $e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $obra = $this->model->findWithProjeto($id);

            if (!$obra) {
                return $this->respondNotFound('Obra não encontrada');
            }

            return $this->respondSuccess($obra);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar obra: ' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            $errors = $this->validateRequired($data, [
                'projeto_id',
                'nome',
                'endereco',
                'municipio',
                'uf'
            ]);

            if (!empty($errors)) {
                return $this->respondValidationError($errors);
            }

            $usuarioId = $this->getUsuarioId();
            $obra = $this->repository->create($data, $usuarioId);

            return $this->respondCreated($obra, 'Obra criada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao criar obra: ' . $e->getMessage(), 500);
        }
    }

    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $obra = $this->repository->find($id);
            if (!$obra) {
                return $this->respondNotFound('Obra não encontrada');
            }

            $data = $this->request->getJSON(true);
            $usuarioId = $this->getUsuarioId();

            $obraAtualizada = $this->repository->update($id, $data, $usuarioId);

            return $this->respondSuccess($obraAtualizada, 'Obra atualizada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao atualizar obra: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $obra = $this->repository->find($id);
            if (!$obra) {
                return $this->respondNotFound('Obra não encontrada');
            }

            $usuarioId = $this->getUsuarioId();
            $this->repository->delete($id, $usuarioId);

            return $this->respondSuccess(null, 'Obra excluída com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao excluir obra: ' . $e->getMessage(), 500);
        }
    }
}
