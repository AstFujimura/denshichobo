<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ControlMigrateCommand extends Command
{
    protected $signature = 'control:migrate
                            {--fresh : Drop all control tables and re-run migrations}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Run migrations for the control database (denshichobo_control)';

    public function handle(): int
    {
        $params = [
            '--database' => 'control',
            '--path' => 'database/migrations/control',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        if ($this->option('fresh')) {
            if (app()->environment('production') && ! $this->option('force')) {
                $this->error('Use --force to run control:migrate --fresh in production.');

                return self::FAILURE;
            }

            $this->call('migrate:fresh', $params);
        } else {
            $this->call('migrate', $params);
        }

        $this->info('Control database migrations completed.');

        return self::SUCCESS;
    }
}
