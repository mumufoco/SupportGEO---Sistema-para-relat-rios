<?php

namespace App\Controllers\API;

use App\Models\EmpresaModel;
use App\Repositories\BaseRepository;

class EmpresaController extends BaseAPIController
{
    private EmpresaModel $model;
    private BaseRepository $repository;

    public function __construct()
    {
        $this->model = new EmpresaModel();
        $this->repository = new BaseRepository($this->model);
    }

    public function index()
    {
        try {
            $params = $this->getPaginationParams();
            $empresas = $this->model->findAll($params['limit'], $params['offset']);

            return $this->respondSuccess($empresas);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar empresas: ' . $e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $empresa = $this->repository->find($id);

            if (!$empresa) {
                return $this->respondNotFound('Empresa não encontrada');
            }

            return $this->respondSuccess($empresa);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar empresa: ' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            $errors = $this->validateRequired($data, [
                'razao_social',
                'cnpj',
                'endereco_completo'
            ]);

            if (!empty($errors)) {
                return $this->respondValidationError($errors);
            }

            $usuarioId = $this->getUsuarioId();
            $empresa = $this->repository->create($data, $usuarioId);

            return $this->respondCreated($empresa, 'Empresa criada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao criar empresa: ' . $e->getMessage(), 500);
        }
    }

    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $empresa = $this->repository->find($id);
            if (!$empresa) {
                return $this->respondNotFound('Empresa não encontrada');
            }

            $data = $this->request->getJSON(true);
            $usuarioId = $this->getUsuarioId();

            $empresaAtualizada = $this->repository->update($id, $data, $usuarioId);

            return $this->respondSuccess($empresaAtualizada, 'Empresa atualizada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao atualizar empresa: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $empresa = $this->repository->find($id);
            if (!$empresa) {
                return $this->respondNotFound('Empresa não encontrada');
            }

            $usuarioId = $this->getUsuarioId();
            $this->repository->delete($id, $usuarioId);

            return $this->respondSuccess(null, 'Empresa excluída com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao excluir empresa: ' . $e->getMessage(), 500);
        }
    }

    public function ativas()
    {
        try {
            $empresas = $this->model->getAtivas();
            return $this->respondSuccess($empresas);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar empresas ativas: ' . $e->getMessage(), 500);
        }
    }

    public function byCnpj($cnpj = null)
    {
        try {
            if (!$cnpj) {
                return $this->respondError('CNPJ não informado', 400);
            }

            $empresa = $this->model->findByCnpj($cnpj);

            if (!$empresa) {
                return $this->respondNotFound('Empresa não encontrada');
            }

            return $this->respondSuccess($empresa);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar empresa: ' . $e->getMessage(), 500);
        }
    }
}
