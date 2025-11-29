<?php

namespace App\Models;

class AmostraModel extends BaseModel
{
    protected string $table = 'amostras';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = false;

    protected array $allowedFields = [
        'sondagem_id',
        'numero_amostra',
        'tipo_perfuracao',
        'profundidade_inicial',
        'profundidade_30cm_1',
        'profundidade_30cm_2',
        'golpes_1a',
        'golpes_2a',
        'golpes_3a',
        'nspt_1a_2a',
        'nspt_2a_3a',
        'penetracao_obtida',
        'limite_golpes',
        'observacoes'
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

    public function calcularNSPT(array $data): array
    {
        $data['nspt_1a_2a'] = ($data['golpes_1a'] ?? 0) + $data['golpes_2a'];
        $data['nspt_2a_3a'] = $data['golpes_2a'] + $data['golpes_3a'];
        return $data;
    }

    public function insert(array $data): array
    {
        $data = $this->calcularNSPT($data);
        return parent::insert($data);
    }

    public function update($id, array $data): array
    {
        if (isset($data['golpes_2a']) || isset($data['golpes_3a'])) {
            $amostra = $this->find($id);
            if ($amostra) {
                $merged = array_merge($amostra, $data);
                $data = $this->calcularNSPT($merged);
            }
        }
        return parent::update($id, $data);
    }

    public function deleteAllBySondagem(int $sondagemId): bool
    {
        return $this->supabase->delete($this->table, [
            'sondagem_id' => $sondagemId
        ]);
    }
}
