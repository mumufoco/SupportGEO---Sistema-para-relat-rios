<?php

namespace App\Models;

class UsuarioModel extends BaseModel
{
    protected string $table = 'usuarios';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = false;

    protected array $allowedFields = [
        'empresa_id',
        'responsavel_tecnico_id',
        'nome',
        'email',
        'password_hash',
        'tipo_usuario',
        'ativo',
        'ultimo_login'
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('email', $email)
            ->first();
    }

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

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if (!$user || !$user['ativo']) {
            return null;
        }

        if (password_verify($password, $user['password_hash'])) {
            $this->update($user['id'], [
                'ultimo_login' => date('Y-m-d H:i:s')
            ]);
            return $user;
        }

        return null;
    }

    public function createUser(array $data): array
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        return $this->insert($data);
    }

    public function updatePassword(int $userId, string $newPassword): array
    {
        return $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function isAdmin(int $userId): bool
    {
        $user = $this->find($userId);
        return $user && $user['tipo_usuario'] === 'admin';
    }

    public function isEngenheiro(int $userId): bool
    {
        $user = $this->find($userId);
        return $user && in_array($user['tipo_usuario'], ['admin', 'engenheiro']);
    }
}
