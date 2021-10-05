<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-xero-timesheet-sync:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Laravel Xero Timesheet Sync requirements';

    /**
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Laravel Xero Timesheet Sync Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-xero-timesheet-sync-config']);

        $this->comment('Publishing Laravel Xero Timesheet Sync Migrations');
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-xero-timesheet-sync-timesheet-table-migrations']);

        $this->info('Laravel Xero Timesheet Sync scaffolding installed successfully.');
    }
}
