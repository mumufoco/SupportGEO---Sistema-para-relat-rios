<?php

namespace App\Controllers\API;

use App\Models\SondagemModel;
use App\Libraries\Queue\RedisQueue;
use CodeIgniter\Database\Exceptions\DatabaseException;

class JobController extends BaseAPIController
{
    protected RedisQueue $queue;
    
    public function __construct()
    {
        $this->queue = new RedisQueue();
    }

    /**
     * Get job status by UUID
     */
    public function show($uuid = null)
    {
        try {
            if (!$uuid) {
                return $this->respondError('Job UUID não informado', 400);
            }

            $db = \Config\Database::connect();
            $job = $db->table('jobs')
                ->where('uuid', $uuid)
                ->get()
                ->getRowArray();

            if (!$job) {
                return $this->respondNotFound('Job não encontrado');
            }

            // Decode JSON fields
            if (isset($job['payload'])) {
                $job['payload'] = json_decode($job['payload'], true);
            }
            if (isset($job['result'])) {
                $job['result'] = json_decode($job['result'], true);
            }

            // Calculate progress
            $progress = $this->calculateProgress($job);

            return $this->respondSuccess([
                'job' => $job,
                'progress' => $progress,
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao buscar job: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List jobs with filters
     */
    public function index()
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('jobs');

            // Apply filters
            if ($status = $this->request->getGet('status')) {
                $builder->where('status', $status);
            }

            if ($type = $this->request->getGet('type')) {
                $builder->where('type', $type);
            }

            if ($queue = $this->request->getGet('queue')) {
                $builder->where('queue', $queue);
            }

            // Pagination
            $page = (int) ($this->request->getGet('page') ?? 1);
            $perPage = (int) ($this->request->getGet('per_page') ?? 20);
            $offset = ($page - 1) * $perPage;

            $total = $builder->countAllResults(false);
            $jobs = $builder
                ->orderBy('created_at', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // Decode JSON fields
            foreach ($jobs as &$job) {
                if (isset($job['payload'])) {
                    $job['payload'] = json_decode($job['payload'], true);
                }
                if (isset($job['result'])) {
                    $job['result'] = json_decode($job['result'], true);
                }
            }

            return $this->respondSuccess([
                'jobs' => $jobs,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($total / $perPage),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao listar jobs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get queue statistics
     */
    public function stats()
    {
        try {
            $queueConfig = config('Queue');
            $stats = [];

            foreach ($queueConfig->queues as $name => $queueKey) {
                $stats[$name] = $this->queue->getStats($queueKey);
            }

            // Get database job stats
            $db = \Config\Database::connect();
            $dbStats = [
                'pending' => $db->table('jobs')->where('status', 'pending')->countAllResults(),
                'processing' => $db->table('jobs')->where('status', 'processing')->countAllResults(),
                'completed' => $db->table('jobs')->where('status', 'completed')->countAllResults(),
                'failed' => $db->table('jobs')->where('status', 'failed')->countAllResults(),
            ];

            return $this->respondSuccess([
                'queue_stats' => $stats,
                'db_stats' => $dbStats,
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Erro ao obter estatísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Retry a failed job
     */
    public function retry($uuid = null)
    {
        try {
            if (!$uuid) {
                return $this->respondError('Job UUID não informado', 400);
            }

            $db = \Config\Database::connect();
            $job = $db->table('jobs')
                ->where('uuid', $uuid)
                ->get()
                ->getRowArray();

            if (!$job) {
                return $this->respondNotFound('Job não encontrado');
            }

            if ($job['status'] !== 'failed') {
                return $this->respondError('Apenas jobs com falha podem ser reprocessados', 400);
            }

            // Reset job status
            $db->table('jobs')
                ->where('uuid', $uuid)
                ->update([
                    'status' => 'pending',
                    'attempts' => 0,
                    'error_message' => null,
                    'failed_at' => null,
                    'available_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            // Re-enqueue
            $payload = json_decode($job['payload'], true);
            $this->queue->push($job['queue'], $payload);

            return $this->respondSuccess(null, 'Job reenfileirado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao reprocessar job: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel a pending job
     */
    public function cancel($uuid = null)
    {
        try {
            if (!$uuid) {
                return $this->respondError('Job UUID não informado', 400);
            }

            $db = \Config\Database::connect();
            $job = $db->table('jobs')
                ->where('uuid', $uuid)
                ->get()
                ->getRowArray();

            if (!$job) {
                return $this->respondNotFound('Job não encontrado');
            }

            if ($job['status'] !== 'pending') {
                return $this->respondError('Apenas jobs pendentes podem ser cancelados', 400);
            }

            $db->table('jobs')
                ->where('uuid', $uuid)
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return $this->respondSuccess(null, 'Job cancelado com sucesso');
        } catch (\Exception $e) {
            return $this->respondError('Erro ao cancelar job: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate job progress percentage
     */
    protected function calculateProgress(array $job): int
    {
        switch ($job['status']) {
            case 'pending':
                return 0;
            case 'processing':
                // Estimate based on attempts or elapsed time
                return min(90, 10 + ($job['attempts'] * 30));
            case 'completed':
                return 100;
            case 'failed':
            case 'cancelled':
                return 0;
            default:
                return 0;
        }
    }
}
