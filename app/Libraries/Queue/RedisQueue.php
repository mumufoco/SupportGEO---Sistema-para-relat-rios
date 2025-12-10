<?php

namespace App\Libraries\Queue;

use Predis\Client;
use Config\Queue as QueueConfig;

class RedisQueue
{
    protected Client $redis;
    protected QueueConfig $config;
    protected string $connection;

    public function __construct(string $connection = 'redis')
    {
        $this->config = config('Queue');
        $this->connection = $connection;
        
        $connConfig = $this->config->connections[$connection];
        
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => $connConfig['host'],
            'port'   => $connConfig['port'],
            'password' => $connConfig['password'] ?: null,
            'database' => $connConfig['database'],
        ]);
    }

    /**
     * Push a job to the queue
     */
    public function push(string $queue, array $job): string
    {
        $jobId = $this->generateJobId();
        $job['id'] = $jobId;
        $job['queue'] = $queue;
        $job['attempts'] = 0;
        $job['created_at'] = time();
        
        $queueKey = $this->getQueueKey($queue);
        $this->redis->lpush($queueKey, [json_encode($job)]);
        
        return $jobId;
    }

    /**
     * Push a delayed job to the queue
     */
    public function later(string $queue, array $job, int $delay): string
    {
        $jobId = $this->generateJobId();
        $job['id'] = $jobId;
        $job['queue'] = $queue;
        $job['attempts'] = 0;
        $job['created_at'] = time();
        $job['available_at'] = time() + $delay;
        
        $delayedKey = $this->getDelayedKey($queue);
        $this->redis->zadd($delayedKey, [$json_encode($job) => $job['available_at']]);
        
        return $jobId;
    }

    /**
     * Pop a job from the queue
     */
    public function pop(string $queue): ?array
    {
        // Move delayed jobs that are ready
        $this->migrateDelayedJobs($queue);
        
        $queueKey = $this->getQueueKey($queue);
        $job = $this->redis->rpop($queueKey);
        
        if (!$job) {
            return null;
        }
        
        return json_decode($job, true);
    }

    /**
     * Reserve a job (blocking pop with timeout)
     */
    public function reserve(string $queue, int $timeout = 5): ?array
    {
        // Move delayed jobs that are ready
        $this->migrateDelayedJobs($queue);
        
        $queueKey = $this->getQueueKey($queue);
        $result = $this->redis->brpop([$queueKey], $timeout);
        
        if (!$result) {
            return null;
        }
        
        return json_decode($result[1], true);
    }

    /**
     * Get queue size
     */
    public function size(string $queue): int
    {
        $queueKey = $this->getQueueKey($queue);
        return (int) $this->redis->llen($queueKey);
    }

    /**
     * Clear a queue
     */
    public function clear(string $queue): bool
    {
        $queueKey = $this->getQueueKey($queue);
        $this->redis->del([$queueKey]);
        
        $delayedKey = $this->getDelayedKey($queue);
        $this->redis->del([$delayedKey]);
        
        return true;
    }

    /**
     * Retry a failed job
     */
    public function retry(array $job): bool
    {
        $job['attempts'] = ($job['attempts'] ?? 0) + 1;
        
        $config = $this->config;
        $maxAttempts = $job['max_attempts'] ?? $config->worker['max_tries'];
        
        if ($job['attempts'] >= $maxAttempts) {
            return $this->fail($job, 'Max attempts reached');
        }
        
        // Calculate delay based on attempt number
        $delay = $config->retry_delays[$job['attempts']] ?? 900;
        
        return (bool) $this->later($job['queue'], $job, $delay);
    }

    /**
     * Mark a job as failed
     */
    public function fail(array $job, string $reason): bool
    {
        $job['failed_at'] = time();
        $job['error'] = $reason;
        
        $failedKey = $this->getFailedKey($job['queue']);
        $this->redis->lpush($failedKey, [json_encode($job)]);
        
        return true;
    }

    /**
     * Get failed jobs
     */
    public function getFailedJobs(string $queue, int $limit = 100): array
    {
        $failedKey = $this->getFailedKey($queue);
        $jobs = $this->redis->lrange($failedKey, 0, $limit - 1);
        
        return array_map(function ($job) {
            return json_decode($job, true);
        }, $jobs);
    }

    /**
     * Migrate delayed jobs that are ready to the main queue
     */
    protected function migrateDelayedJobs(string $queue): void
    {
        $delayedKey = $this->getDelayedKey($queue);
        $now = time();
        
        // Get all delayed jobs that are ready
        $jobs = $this->redis->zrangebyscore($delayedKey, '-inf', (string) $now);
        
        if (empty($jobs)) {
            return;
        }
        
        $queueKey = $this->getQueueKey($queue);
        
        foreach ($jobs as $job) {
            $this->redis->lpush($queueKey, [$job]);
            $this->redis->zrem($delayedKey, $job);
        }
    }

    /**
     * Generate a unique job ID
     */
    protected function generateJobId(): string
    {
        return uniqid('job_', true);
    }

    /**
     * Get the Redis key for a queue
     */
    protected function getQueueKey(string $queue): string
    {
        $prefix = $this->config->connections[$this->connection]['prefix'];
        return $prefix . $queue;
    }

    /**
     * Get the Redis key for delayed jobs
     */
    protected function getDelayedKey(string $queue): string
    {
        $prefix = $this->config->connections[$this->connection]['prefix'];
        return $prefix . $queue . ':delayed';
    }

    /**
     * Get the Redis key for failed jobs
     */
    protected function getFailedKey(string $queue): string
    {
        $prefix = $this->config->connections[$this->connection]['prefix'];
        return $prefix . $queue . ':failed';
    }

    /**
     * Get statistics for a queue
     */
    public function getStats(string $queue): array
    {
        return [
            'size' => $this->size($queue),
            'delayed' => (int) $this->redis->zcard($this->getDelayedKey($queue)),
            'failed' => (int) $this->redis->llen($this->getFailedKey($queue)),
        ];
    }
}
