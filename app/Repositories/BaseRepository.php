<?php

namespace App\Repositories;

use App\Models\BaseModel;
use App\Models\AuditLogModel;

abstract class BaseRepository
{
    protected BaseModel $model;
    protected AuditLogModel $auditLog;
    protected bool $enableAudit = true;

    public function __construct(BaseModel $model)
    {
        $this->model = $model;
        $this->auditLog = new AuditLogModel();
    }

    public function find($id): ?array
    {
        return $this->model->find($id);
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return $this->model->findAll($limit, $offset);
    }

    public function create(array $data, ?int $usuarioId = null): array
    {
        $result = $this->model->insert($data);

        if ($this->enableAudit && !empty($result)) {
            $id = $result[0]['id'] ?? null;
            if ($id) {
                $this->auditLog->log(
                    $usuarioId,
                    $this->model->getTable(),
                    $id,
                    'create',
                    null,
                    $result[0]
                );
            }
        }

        return $result;
    }

    public function update($id, array $data, ?int $usuarioId = null): array
    {
        $oldData = $this->enableAudit ? $this->model->find($id) : null;

        $result = $this->model->update($id, $data);

        if ($this->enableAudit && !empty($result)) {
            $this->auditLog->log(
                $usuarioId,
                $this->model->getTable(),
                $id,
                'update',
                $oldData,
                $result[0] ?? null
            );
        }

        return $result;
    }

    public function delete($id, ?int $usuarioId = null): bool
    {
        $oldData = $this->enableAudit ? $this->model->find($id) : null;

        $result = $this->model->delete($id);

        if ($this->enableAudit && $result) {
            $this->auditLog->log(
                $usuarioId,
                $this->model->getTable(),
                $id,
                'delete',
                $oldData,
                null
            );
        }

        return $result;
    }

    public function where(string $column, $value): array
    {
        return $this->model->where($column, $value);
    }

    public function getModel(): BaseModel
    {
        return $this->model;
    }

    protected function logCustomAction(
        int $registroId,
        string $acao,
        ?int $usuarioId = null,
        ?array $dadosAntigos = null,
        ?array $dadosNovos = null
    ): void {
        if ($this->enableAudit) {
            $this->auditLog->log(
                $usuarioId,
                $this->model->getTable(),
                $registroId,
                $acao,
                $dadosAntigos,
                $dadosNovos
            );
        }
    }
}
