<?php

namespace App\Repositories;

use App\Models\CamadaModel;

class CamadaRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CamadaModel());
    }

    public function findBySondagem(int $sondagemId): array
    {
        return $this->model->findBySondagem($sondagemId);
    }

    public function getPerfilEstratigrafico(int $sondagemId): array
    {
        return $this->model->getPerfilEstratigrafico($sondagemId);
    }

    public function createBatch(array $camadas, int $sondagemId, ?int $usuarioId = null): array
    {
        $results = [];

        foreach ($camadas as $camada) {
            $camada['sondagem_id'] = $sondagemId;
            $results[] = $this->create($camada, $usuarioId);
        }

        return $results;
    }

    public function deleteAllBySondagem(int $sondagemId, ?int $usuarioId = null): bool
    {
        if ($this->enableAudit) {
            $camadas = $this->model->findBySondagem($sondagemId);
            foreach ($camadas as $camada) {
                $this->auditLog->log(
                    $usuarioId,
                    $this->model->getTable(),
                    $camada['id'],
                    'delete',
                    $camada,
                    null
                );
            }
        }

        return $this->model->deleteAllBySondagem($sondagemId);
    }
}
