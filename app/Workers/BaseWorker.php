<?php

namespace App\Workers;

use App\Libraries\Queue\RedisQueue;
use Config\Queue as QueueConfig;

abstract class BaseWorker
{
    protected RedisQueue $queue;
    protected QueueConfig $config;
    protected string $queueName;
    protected bool $shouldQuit = false;

    public function __construct(string $queueName)
    {
        $this->queueName = $queueName;
        $this->queue = new RedisQueue();
        $this->config = config('Queue');
        
        // Register signal handlers for graceful shutdown
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }
    }

    /**
     * Start the worker
     */
    public function run(): void
    {
        log_message('info', "Worker started for queue: {$this->queueName}");
        
        $jobsProcessed = 0;
        $maxJobs = $this->config->worker['max_jobs'];
        $sleepSeconds = $this->config->worker['sleep'];
        
        while (!$this->shouldQuit && $jobsProcessed < $maxJobs) {
            // Check memory limit
            if ($this->memoryExceeded()) {
                log_message('warning', 'Worker memory limit exceeded, stopping...');
                break;
            }
            
            // Process signals
            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }
            
            // Get next job
            $job = $this->queue->reserve($this->queueName, $sleepSeconds);
            
            if ($job === null) {
                continue;
            }
            
            try {
                log_message('info', "Processing job: {$job['id']}");
                
                $startTime = microtime(true);
                $result = $this->process($job);
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                if ($result) {
                    log_message('info', "Job {$job['id']} completed in {$duration}ms");
                    $jobsProcessed++;
                } else {
                    log_message('error', "Job {$job['id']} failed");
                    $this->queue->retry($job);
                }
            } catch (\Throwable $e) {
                log_message('error', "Job {$job['id']} exception: {$e->getMessage()}");
                $this->queue->retry($job);
            }
        }
        
        log_message('info', "Worker stopped. Processed {$jobsProcessed} jobs.");
    }

    /**
     * Process a job - to be implemented by child classes
     */
    abstract protected function process(array $job): bool;

    /**
     * Handle shutdown signals
     */
    public function handleSignal(int $signal): void
    {
        log_message('info', "Worker received signal: {$signal}");
        $this->shouldQuit = true;
    }

    /**
     * Check if memory limit is exceeded
     */
    protected function memoryExceeded(): bool
    {
        $memoryLimit = $this->config->worker['memory_limit'];
        $currentMemory = memory_get_usage(true) / 1024 / 1024; // MB
        
        return $currentMemory >= $memoryLimit;
    }

    /**
     * Get job data safely
     */
    protected function getJobData(array $job, string $key, $default = null)
    {
        return $job['data'][$key] ?? $default;
    }
}
