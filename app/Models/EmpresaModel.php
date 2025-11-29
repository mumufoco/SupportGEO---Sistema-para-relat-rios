<?php

namespace App\Models;

class EmpresaModel extends BaseModel
{
    protected string $table = 'empresas';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = true;

    protected array $allowedFields = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'crea_empresa',
        'endereco_completo',
        'endereco_filial',
        'municipio',
        'uf',
        'cep',
        'telefone',
        'email',
        'website',
        'logo_path',
        'ativo'
    ];

    public function findByCnpj(string $cnpj): ?array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('cnpj', $cnpj)
            ->first();
    }

    public function getAtivas(): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('ativo', 'true')
            ->eq($this->deletedField, null)
            ->order('razao_social', 'asc')
            ->get();
    }
}
