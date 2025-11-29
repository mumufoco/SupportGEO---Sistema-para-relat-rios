<?php

namespace App\Models;

class ProjetoModel extends BaseModel
{
    protected string $table = 'projetos';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = true;

    protected array $allowedFields = [
        'empresa_id',
        'nome',
        'codigo',
        'cliente',
        'cnpj_cliente',
        'descricao',
        'data_inicio',
        'data_previsao_termino',
        'status'
    ];

    public function findByEmpresa(int $empresaId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('empresa_id', $empresaId)
            ->eq($this->deletedField, null)
            ->order('data_inicio', 'desc')
            ->get();
    }

    public function findAtivos(int $empresaId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('empresa_id', $empresaId)
            ->eq('status', 'ativo')
            ->eq($this->deletedField, null)
            ->order('nome', 'asc')
            ->get();
    }

    public function findByCodigo(string $codigo): ?array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('codigo', $codigo)
            ->first();
    }
}
