<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Workers\PDFWorker;
use App\Workers\ImageWorker;
use App\Workers\ImportWorker;

class WorkerCommand extends BaseCommand
{
    protected $group       = 'Queue';
    protected $name        = 'worker:run';
    protected $description = 'Run a queue worker';
    protected $usage       = 'worker:run [worker_type]';
    protected $arguments   = [
        'worker_type' => 'Type of worker to run (pdf, image, import, or all)',
    ];

    public function run(array $params)
    {
        $workerType = $params[0] ?? 'all';
        
        CLI::write("Starting worker: {$workerType}", 'green');
        CLI::write("Press Ctrl+C to stop the worker gracefully", 'yellow');
        CLI::newLine();
        
        try {
            switch ($workerType) {
                case 'pdf':
                    $this->runPDFWorker();
                    break;
                
                case 'image':
                    $this->runImageWorker();
                    break;
                
                case 'import':
                    $this->runImportWorker();
                    break;
                
                case 'all':
                    $this->runAllWorkers();
                    break;
                
                default:
                    CLI::error("Unknown worker type: {$workerType}");
                    CLI::write("Available types: pdf, image, import, all");
                    return;
            }
        } catch (\Throwable $e) {
            CLI::error("Worker error: {$e->getMessage()}");
            log_message('error', "Worker error: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }
    }

    protected function runPDFWorker(): void
    {
        CLI::write('PDF Worker started', 'green');
        $worker = new PDFWorker();
        $worker->run();
        CLI::write('PDF Worker stopped', 'yellow');
    }

    protected function runImageWorker(): void
    {
        CLI::write('Image Worker started', 'green');
        $worker = new ImageWorker();
        $worker->run();
        CLI::write('Image Worker stopped', 'yellow');
    }

    protected function runImportWorker(): void
    {
        CLI::write('Import Worker started', 'green');
        $worker = new ImportWorker();
        $worker->run();
        CLI::write('Import Worker stopped', 'yellow');
    }

    protected function runAllWorkers(): void
    {
        CLI::write('Running all workers (This is a demo - use supervisor in production)', 'yellow');
        CLI::write('For production, start each worker type separately', 'yellow');
        CLI::newLine();
        
        // In production, each worker should run in its own process via supervisor
        // This is just for demonstration/development
        
        $pids = [];
        
        if (function_exists('pcntl_fork')) {
            // Fork process for PDF worker
            $pid = pcntl_fork();
            if ($pid == -1) {
                CLI::error('Could not fork PDF worker');
            } elseif ($pid == 0) {
                // Child process - PDF worker
                $this->runPDFWorker();
                exit(0);
            } else {
                $pids[] = $pid;
            }
            
            // Fork process for Image worker
            $pid = pcntl_fork();
            if ($pid == -1) {
                CLI::error('Could not fork Image worker');
            } elseif ($pid == 0) {
                // Child process - Image worker
                $this->runImageWorker();
                exit(0);
            } else {
                $pids[] = $pid;
            }
            
            // Fork process for Import worker
            $pid = pcntl_fork();
            if ($pid == -1) {
                CLI::error('Could not fork Import worker');
            } elseif ($pid == 0) {
                // Child process - Import worker
                $this->runImportWorker();
                exit(0);
            } else {
                $pids[] = $pid;
            }
            
            // Parent process - wait for all children
            foreach ($pids as $pid) {
                pcntl_waitpid($pid, $status);
            }
        } else {
            CLI::error('pcntl extension not available. Please run each worker type separately.');
            CLI::write('Example: php spark worker:run pdf');
        }
    }
}
