<?php

namespace App\Repositories;

use App\Models\AmostraModel;

class AmostraRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AmostraModel());
    }

    public function findBySondagem(int $sondagemId): array
    {
        return $this->model->findBySondagem($sondagemId);
    }

    public function createBatch(array $amostras, int $sondagemId, ?int $usuarioId = null): array
    {
        $results = [];

        foreach ($amostras as $amostra) {
            $amostra['sondagem_id'] = $sondagemId;
            $results[] = $this->create($amostra, $usuarioId);
        }

        return $results;
    }

    public function deleteAllBySondagem(int $sondagemId, ?int $usuarioId = null): bool
    {
        if ($this->enableAudit) {
            $amostras = $this->model->findBySondagem($sondagemId);
            foreach ($amostras as $amostra) {
                $this->auditLog->log(
                    $usuarioId,
                    $this->model->getTable(),
                    $amostra['id'],
                    'delete',
                    $amostra,
                    null
                );
            }
        }

        return $this->model->deleteAllBySondagem($sondagemId);
    }

    public function getGraficoNSPT(int $sondagemId): array
    {
        $amostras = $this->findBySondagem($sondagemId);

        $dados = [];
        foreach ($amostras as $amostra) {
            $dados[] = [
                'profundidade' => $amostra['profundidade_inicial'],
                'nspt' => $amostra['nspt_2a_3a'],
                'numero' => $amostra['numero_amostra']
            ];
        }

        return $dados;
    }
}
