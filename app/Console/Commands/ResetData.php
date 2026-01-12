<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:reset {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate customers, sales, sale_items, and products tables';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            $this->error('Application In Production!');
            if (!$this->confirm('Do you really wish to run this command?')) {
                return;
            }
        }

        $tables = [
            'sale_items',
            'sales',
            'customers',
            'products',
            'file_uploads'
        ];

        $this->info('Truncating tables: ' . implode(', ', $tables));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("- Truncated <info>$table</info>");
            } else {
                $this->warn("- Table <error>$table</error> not found");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Data reset successfully.');
    }
}
