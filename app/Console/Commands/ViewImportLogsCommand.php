<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ViewImportLogsCommand extends Command
{
    protected $signature = 'logs:import {lines=50}';
    protected $description = 'View recent import logs';

    public function handle()
    {
        $lines = $this->argument('lines');
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            $this->error('Log file not found: ' . $logFile);
            return 1;
        }

        $this->info("Showing last $lines lines from import logs:");
        $this->line('');

        // Use tail to get the last N lines
        $command = "tail -$lines '$logFile' | grep -E '(import|Import|Row|Error|WARNING|ERROR)'";
        $output = shell_exec($command);

        if ($output) {
            $this->line($output);
        } else {
            $this->info('No import-related logs found in the last ' . $lines . ' lines.');
        }

        return 0;
    }
}