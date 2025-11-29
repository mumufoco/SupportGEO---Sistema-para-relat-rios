<?php

namespace App\Models;

class ResponsavelTecnicoModel extends BaseModel
{
    protected string $table = 'responsaveis_tecnicos';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = false;

    protected array $allowedFields = [
        'empresa_id',
        'nome',
        'crea',
        'cargo',
        'email',
        'telefone',
        'assinatura_path',
        'ativo'
    ];

    public function findByEmpresa(int $empresaId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('empresa_id', $empresaId)
            ->eq('ativo', 'true')
            ->order('nome', 'asc')
            ->get();
    }

    public function findByCrea(string $crea): ?array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('crea', $crea)
            ->first();
    }
}
