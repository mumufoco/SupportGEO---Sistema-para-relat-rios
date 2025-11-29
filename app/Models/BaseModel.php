<?php

namespace App\Models;

use App\Libraries\SupabaseClient;

abstract class BaseModel
{
    protected SupabaseClient $supabase;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $allowedFields = [];
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = false;

    protected string $createdField = 'created_at';
    protected string $updatedField = 'updated_at';
    protected string $deletedField = 'deleted_at';

    public function __construct()
    {
        $this->supabase = new SupabaseClient();
    }

    public function find($id): ?array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq($this->primaryKey, $id)
            ->first();
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $query = $this->supabase
            ->from($this->table)
            ->select('*')
            ->limit($limit);

        if ($this->useSoftDeletes) {
            $query->eq($this->deletedField, null);
        }

        return $query->get();
    }

    public function insert(array $data): array
    {
        $data = $this->filterAllowedFields($data);

        if ($this->useTimestamps) {
            $data[$this->createdField] = date('Y-m-d H:i:s');
            $data[$this->updatedField] = date('Y-m-d H:i:s');
        }

        return $this->supabase->insert($this->table, $data);
    }

    public function update($id, array $data): array
    {
        $data = $this->filterAllowedFields($data);

        if ($this->useTimestamps) {
            $data[$this->updatedField] = date('Y-m-d H:i:s');
        }

        return $this->supabase->update($this->table, $data, [
            $this->primaryKey => $id
        ]);
    }

    public function delete($id): bool
    {
        if ($this->useSoftDeletes) {
            return !empty($this->supabase->update($this->table, [
                $this->deletedField => date('Y-m-d H:i:s')
            ], [
                $this->primaryKey => $id
            ]));
        }

        return $this->supabase->delete($this->table, [
            $this->primaryKey => $id
        ]);
    }

    public function where(string $column, $value): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq($column, $value)
            ->get();
    }

    protected function filterAllowedFields(array $data): array
    {
        if (empty($this->allowedFields)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->allowedFields));
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
