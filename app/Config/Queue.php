<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Queue Configuration
 */
class Queue extends BaseConfig
{
    /**
     * Default queue connection
     */
    public string $default = 'redis';

    /**
     * Queue connections
     */
    public array $connections = [
        'redis' => [
            'driver'     => 'redis',
            'host'       => 'redis',
            'port'       => 6379,
            'password'   => '',
            'database'   => 0,
            'prefix'     => 'queue:',
            'timeout'    => 60,
            'retry_after' => 90,
        ],
    ];

    /**
     * Queue names
     */
    public array $queues = [
        'default'  => 'default',
        'pdf'      => 'pdf-generation',
        'image'    => 'image-processing',
        'import'   => 'data-import',
        'email'    => 'email-sending',
    ];

    /**
     * Worker settings
     */
    public array $worker = [
        'max_tries'      => 3,
        'timeout'        => 300,
        'sleep'          => 3,
        'max_jobs'       => 1000,
        'memory_limit'   => 256,
    ];

    /**
     * Job retry delays (in seconds)
     */
    public array $retry_delays = [
        1 => 60,      // First retry after 1 minute
        2 => 300,     // Second retry after 5 minutes
        3 => 900,     // Third retry after 15 minutes
    ];

    public function __construct()
    {
        parent::__construct();

        // Override with environment variables
        if ($host = env('REDIS_HOST')) {
            $this->connections['redis']['host'] = $host;
        }

        if ($port = env('REDIS_PORT')) {
            $this->connections['redis']['port'] = (int) $port;
        }

        if ($password = env('REDIS_PASSWORD')) {
            $this->connections['redis']['password'] = $password;
        }

        if ($database = env('REDIS_DATABASE')) {
            $this->connections['redis']['database'] = (int) $database;
        }

        if ($prefix = env('REDIS_PREFIX')) {
            $this->connections['redis']['prefix'] = $prefix;
        }
    }
}
