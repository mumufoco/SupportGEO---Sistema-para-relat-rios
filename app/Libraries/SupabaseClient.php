<?php

namespace App\Libraries;

class SupabaseClient
{
    private string $supabaseUrl;
    private string $supabaseKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_ANON_KEY');
        $this->apiUrl = $this->supabaseUrl . '/rest/v1';
    }

    public function from(string $table): SupabaseQueryBuilder
    {
        return new SupabaseQueryBuilder($this, $table);
    }

    public function executeQuery(string $table, array $params = []): array
    {
        $url = $this->apiUrl . '/' . $table;

        if (!empty($params['select'])) {
            $url .= '?select=' . urlencode($params['select']);
        }

        if (!empty($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $separator = strpos($url, '?') !== false ? '&' : '?';
                $url .= $separator . urlencode($key) . '=' . urlencode($value);
            }
        }

        if (!empty($params['order'])) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . 'order=' . urlencode($params['order']);
        }

        if (!empty($params['limit'])) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . 'limit=' . $params['limit'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \Exception("Supabase API Error: HTTP $httpCode - $response");
        }

        return json_decode($response, true) ?? [];
    }

    public function insert(string $table, array $data): array
    {
        $url = $this->apiUrl . '/' . $table;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \Exception("Supabase Insert Error: HTTP $httpCode - $response");
        }

        return json_decode($response, true) ?? [];
    }

    public function update(string $table, array $data, array $conditions): array
    {
        $url = $this->apiUrl . '/' . $table;

        foreach ($conditions as $key => $value) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . urlencode($key) . '=eq.' . urlencode($value);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \Exception("Supabase Update Error: HTTP $httpCode - $response");
        }

        return json_decode($response, true) ?? [];
    }

    public function delete(string $table, array $conditions): bool
    {
        $url = $this->apiUrl . '/' . $table;

        foreach ($conditions as $key => $value) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . urlencode($key) . '=eq.' . urlencode($value);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}

class SupabaseQueryBuilder
{
    private SupabaseClient $client;
    private string $table;
    private array $params = [];

    public function __construct(SupabaseClient $client, string $table)
    {
        $this->client = $client;
        $this->table = $table;
    }

    public function select(string $columns = '*'): self
    {
        $this->params['select'] = $columns;
        return $this;
    }

    public function eq(string $column, $value): self
    {
        $this->params['filter'][$column] = 'eq.' . $value;
        return $this;
    }

    public function order(string $column, string $direction = 'asc'): self
    {
        $this->params['order'] = $column . '.' . $direction;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->params['limit'] = $limit;
        return $this;
    }

    public function get(): array
    {
        return $this->client->executeQuery($this->table, $this->params);
    }

    public function first(): ?array
    {
        $this->params['limit'] = 1;
        $results = $this->client->executeQuery($this->table, $this->params);
        return $results[0] ?? null;
    }
}
