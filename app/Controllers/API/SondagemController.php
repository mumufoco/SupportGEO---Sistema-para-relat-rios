<?php

namespace App\Controllers\API;

use App\Models\SondagemModel;
use App\Repositories\SondagemRepository;
use App\Repositories\CamadaRepository;
use App\Repositories\AmostraRepository;
use App\Libraries\NBR\NBRValidator;

class SondagemController extends BaseAPIController
{
    private SondagemModel $model;
    private SondagemRepository $repository;
    private CamadaRepository $camadaRepo;
    private AmostraRepository $amostraRepo;
    private NBRValidator $validator;

    public function __construct()
    {
        $this->model = new SondagemModel();
        $this->repository = new SondagemRepository();
        $this->camadaRepo = new CamadaRepository();
        $this->amostraRepo = new AmostraRepository();
        $this->validator = new NBRValidator();
    }

    public function index()
    {
        try {
            $obraId = $this->request->getGet('obra_id');
            $status = $this->request->getGet('status');

            if ($obraId) {
                $sondagens = $this->repository->findByObra($obraId);
            } elseif ($status) {
                $sondagens = $this->model->findByStatus($status);
            } else {
                $params = $this->getPaginationParams();
                $sondagens = $this->model->findAll($params['limit'], $params['offset']);
            }

            return $this->respondSuccess($sondagens);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar sondagens: ' . $e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $sondagem = $this->repository->findWithRelations($id);

            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            return $this->respondSuccess($sondagem);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            $errors = $this->validateRequired($data, [
                'obra_id',
                'codigo_sondagem',
                'data_execucao',
                'coordenada_este',
                'coordenada_norte'
            ]);

            if (!empty($errors)) {
                return $this->respondValidationError($errors);
            }

            $usuarioId = $this->getUsuarioId();
            $sondagem = $this->repository->create($data, $usuarioId);

            return $this->respondCreated($sondagem, 'Sondagem criada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao criar sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $sondagem = $this->repository->find($id);
            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $data = $this->request->getJSON(true);
            $usuarioId = $this->getUsuarioId();

            $sondagemAtualizada = $this->repository->update($id, $data, $usuarioId);

            return $this->respondSuccess($sondagemAtualizada, 'Sondagem atualizada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao atualizar sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $sondagem = $this->repository->find($id);
            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $usuarioId = $this->getUsuarioId();
            $this->repository->delete($id, $usuarioId);

            return $this->respondSuccess(null, 'Sondagem excluída com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao excluir sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function aprovar($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $usuarioId = $this->requireAuth();

            $sondagem = $this->repository->find($id);
            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $resultado = $this->repository->aprovar($id, $usuarioId);

            return $this->respondSuccess($resultado, 'Sondagem aprovada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao aprovar sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function rejeitar($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $usuarioId = $this->requireAuth();

            $sondagem = $this->repository->find($id);
            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $resultado = $this->repository->rejeitar($id, $usuarioId);

            return $this->respondSuccess($resultado, 'Sondagem rejeitada');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao rejeitar sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function duplicar($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $data = $this->request->getJSON(true);

            if (empty($data['codigo_sondagem'])) {
                return $this->respondError('Novo código de sondagem não informado', 400);
            }

            $usuarioId = $this->requireAuth();

            $novaSondagem = $this->repository->duplicar($id, $data, $usuarioId);

            if (!$novaSondagem) {
                return $this->respondError('Erro ao duplicar sondagem', 500);
            }

            return $this->respondCreated($novaSondagem, 'Sondagem duplicada com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao duplicar sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function validar($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $sondagem = $this->repository->find($id);
            if (!$sondagem) {
                return $this->respondNotFound('Sondagem não encontrada');
            }

            $resultado = $this->validator->validateSondagem($sondagem);

            return $this->respondSuccess($resultado);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao validar sondagem: ' . $e->getMessage(), 500);
        }
    }

    public function calcularConformidade($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $score = $this->repository->calcularConformidadeNBR($id);

            return $this->respondSuccess([
                'score' => $score,
                'conforme' => $score >= 70
            ], 'Score de conformidade calculado');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao calcular conformidade: ' . $e->getMessage(), 500);
        }
    }

    public function camadas($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $camadas = $this->camadaRepo->findBySondagem($id);
            return $this->respondSuccess($camadas);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar camadas: ' . $e->getMessage(), 500);
        }
    }

    public function amostras($id = null)
    {
        try {
            if (!$id) {
                return $this->respondError('ID não informado', 400);
            }

            $amostras = $this->amostraRepo->findBySondagem($id);
            return $this->respondSuccess($amostras);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar amostras: ' . $e->getMessage(), 500);
        }
    }
}
