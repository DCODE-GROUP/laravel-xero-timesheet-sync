<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Commands;

use Dcodegroup\LaravelConfiguration\Models\Configuration;
use Dcodegroup\LaravelXeroPayrollAu\Jobs\SyncPayrollConfigurationOptions;
use Illuminate\Console\Command;

class AutoUpdateXeroConfigurationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-xero-timesheet-sync:update-xero-configuration-data
         {--force : force update now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the configuration data weekly for Leave Types, Earning Rates and Payroll Calendars';

    /**
     * @return void
     */
    public function handle()
    {
        $force = $this->option('force');
        $record = Configuration::byKey('xero_payroll_calendars')->first();

        if ($record->updated_at->gte(now()->subWeek()) || $force) {
            SyncPayrollConfigurationOptions::dispatch();
            $this->info('Laravel Xero Timesheet Sync Xero Configuration data updated.');
        } else {
            $this->info('Laravel Xero Timesheet Sync Xero Configuration update not required.');
        }
    }
}
