<?php

namespace App\Models;

class CamadaModel extends BaseModel
{
    protected string $table = 'camadas';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = false;

    protected array $allowedFields = [
        'sondagem_id',
        'numero_camada',
        'profundidade_inicial',
        'profundidade_final',
        'classificacao_principal',
        'classificacao_secundaria',
        'descricao_completa',
        'cor',
        'origem',
        'consistencia',
        'compacidade',
        'amostras_ids',
        'cor_grafico'
    ];

    public function findBySondagem(int $sondagemId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('sondagem_id', $sondagemId)
            ->order('profundidade_inicial', 'asc')
            ->get();
    }

    public function getPerfilEstratigrafico(int $sondagemId): array
    {
        return $this->findBySondagem($sondagemId);
    }

    public function deleteAllBySondagem(int $sondagemId): bool
    {
        return $this->supabase->delete($this->table, [
            'sondagem_id' => $sondagemId
        ]);
    }
}
