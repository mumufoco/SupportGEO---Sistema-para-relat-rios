<?php

namespace App\Controllers\API;

use App\Models\ProjetoModel;
use App\Repositories\BaseRepository;

class ProjetoController extends BaseAPIController
{
    private ProjetoModel $model;
    private BaseRepository $repository;

    public function __construct()
    {
        $this->model = new ProjetoModel();
        $this->repository = new BaseRepository($this->model);
    }

    public function index()
    {
        try {
            $empresaId = $this->request->getGet('empresa_id');

            if ($empresaId) {
                $projetos = $this->model->findByEmpresa($empresaId);
            } else {
                $params = $this->getPaginationParams();
                $projetos = $this->model->findAll($params['limit'], $params['offset']);
            }

            return $this->respondSuccess($projetos);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar projetos: ' . $e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $projeto = $this->repository->find($id);

            if (!$projeto) {
                return $this->respondNotFound('Projeto não encontrado');
            }

            return $this->respondSuccess($projeto);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar projeto: ' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            $errors = $this->validateRequired($data, [
                'empresa_id',
                'nome',
                'cliente'
            ]);

            if (!empty($errors)) {
                return $this->respondValidationError($errors);
            }

            $usuarioId = $this->getUsuarioId();
            $projeto = $this->repository->create($data, $usuarioId);

            return $this->respondCreated($projeto, 'Projeto criado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao criar projeto: ' . $e->getMessage(), 500);
        }
    }

    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $projeto = $this->repository->find($id);
            if (!$projeto) {
                return $this->respondNotFound('Projeto não encontrado');
            }

            $data = $this->request->getJSON(true);
            $usuarioId = $this->getUsuarioId();

            $projetoAtualizado = $this->repository->update($id, $data, $usuarioId);

            return $this->respondSuccess($projetoAtualizado, 'Projeto atualizado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao atualizar projeto: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $projeto = $this->repository->find($id);
            if (!$projeto) {
                return $this->respondNotFound('Projeto não encontrado');
            }

            $usuarioId = $this->getUsuarioId();
            $this->repository->delete($id, $usuarioId);

            return $this->respondSuccess(null, 'Projeto excluído com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao excluir projeto: ' . $e->getMessage(), 500);
        }
    }

    public function ativos($empresaId = null)
    {
        try {
            if (!$empresaId) {
                return $this->respondError('ID da empresa não informado', 400);
            }

            $projetos = $this->model->findAtivos($empresaId);
            return $this->respondSuccess($projetos);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar projetos ativos: ' . $e->getMessage(), 500);
        }
    }
}
