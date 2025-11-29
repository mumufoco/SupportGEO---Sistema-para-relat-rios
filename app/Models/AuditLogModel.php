<?php

namespace App\Models;

class AuditLogModel extends BaseModel
{
    protected string $table = 'audit_log';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = false;
    protected bool $useSoftDeletes = false;

    protected array $allowedFields = [
        'usuario_id',
        'tabela',
        'registro_id',
        'acao',
        'dados_antigos',
        'dados_novos',
        'ip_address',
        'user_agent'
    ];

    public function log(
        ?int $usuarioId,
        string $tabela,
        int $registroId,
        string $acao,
        ?array $dadosAntigos = null,
        ?array $dadosNovos = null
    ): array {
        $data = [
            'usuario_id' => $usuarioId,
            'tabela' => $tabela,
            'registro_id' => $registroId,
            'acao' => $acao,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($dadosAntigos !== null) {
            $data['dados_antigos'] = json_encode($dadosAntigos);
        }

        if ($dadosNovos !== null) {
            $data['dados_novos'] = json_encode($dadosNovos);
        }

        return $this->supabase->insert($this->table, $data);
    }

    public function findByTabela(string $tabela, int $registroId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('tabela', $tabela)
            ->eq('registro_id', $registroId)
            ->order('created_at', 'desc')
            ->get();
    }

    public function findByUsuario(int $usuarioId, int $limit = 100): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('usuario_id', $usuarioId)
            ->order('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function findRecent(int $limit = 50): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->order('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function findByAcao(string $acao, int $limit = 100): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('acao', $acao)
            ->order('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
