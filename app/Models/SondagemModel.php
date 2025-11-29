<?php

namespace App\Models;

class SondagemModel extends BaseModel
{
    protected string $table = 'sondagens';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = true;

    protected array $allowedFields = [
        'obra_id',
        'responsavel_tecnico_id',
        'codigo_sondagem',
        'identificacao_cliente',
        'data_execucao',
        'hora_inicio',
        'hora_termino',
        'sondador',
        'auxiliares',
        'coordenada_este',
        'coordenada_norte',
        'cota_boca_furo',
        'nivel_agua_inicial',
        'nivel_agua_inicial_profundidade',
        'nivel_agua_inicial_data',
        'nivel_agua_final',
        'nivel_agua_final_profundidade',
        'nivel_agua_final_data',
        'revestimento_profundidade',
        'profundidade_trado',
        'profundidade_final',
        'peso_martelo',
        'altura_queda',
        'diametro_amostrador_externo',
        'diametro_amostrador_interno',
        'diametro_revestimento',
        'diametro_trado',
        'sistema_percussao',
        'escala_vertical',
        'escala_horizontal',
        'observacoes_gerais',
        'observacoes_paralisacao',
        'versao',
        'status',
        'aprovado_por',
        'data_aprovacao',
        'score_conformidade',
        'ultima_verificacao_nbr'
    ];

    public function findByObra(int $obraId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('obra_id', $obraId)
            ->eq($this->deletedField, null)
            ->order('codigo_sondagem', 'asc')
            ->get();
    }

    public function findByCodigo(string $codigo, int $obraId): ?array
    {
        $results = $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('codigo_sondagem', $codigo)
            ->eq('obra_id', $obraId)
            ->get();

        return $results[0] ?? null;
    }

    public function findAprovadas(int $obraId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('obra_id', $obraId)
            ->eq('status', 'aprovado')
            ->eq($this->deletedField, null)
            ->order('codigo_sondagem', 'asc')
            ->get();
    }

    public function findByStatus(string $status): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('status', $status)
            ->eq($this->deletedField, null)
            ->order('data_execucao', 'desc')
            ->get();
    }

    public function aprovar(int $sondagemId, int $usuarioId): array
    {
        return $this->update($sondagemId, [
            'status' => 'aprovado',
            'aprovado_por' => $usuarioId,
            'data_aprovacao' => date('Y-m-d H:i:s')
        ]);
    }

    public function rejeitar(int $sondagemId): array
    {
        return $this->update($sondagemId, [
            'status' => 'rejeitado'
        ]);
    }

    public function updateConformidade(int $sondagemId, int $score): array
    {
        return $this->update($sondagemId, [
            'score_conformidade' => $score,
            'ultima_verificacao_nbr' => date('Y-m-d H:i:s')
        ]);
    }
}
