<?php

namespace App\Models;

class ObraModel extends BaseModel
{
    protected string $table = 'obras';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = true;

    protected array $allowedFields = [
        'projeto_id',
        'nome',
        'endereco',
        'municipio',
        'uf',
        'cep',
        'datum',
        'zona_utm',
        'observacoes'
    ];

    public function findByProjeto(int $projetoId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('projeto_id', $projetoId)
            ->eq($this->deletedField, null)
            ->order('nome', 'asc')
            ->get();
    }

    public function findWithProjeto(int $obraId): ?array
    {
        $obra = $this->find($obraId);

        if ($obra && !empty($obra['projeto_id'])) {
            $projetoModel = new ProjetoModel();
            $obra['projeto'] = $projetoModel->find($obra['projeto_id']);
        }

        return $obra;
    }
}
