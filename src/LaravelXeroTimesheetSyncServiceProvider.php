<?php

namespace Dcodegroup\LaravelXeroTimesheetSync;

use Illuminate\Support\ServiceProvider;
use XeroPHP\Application;

class LaravelXeroTimesheetSyncServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->offerPublishing();

        $timesheetClass = config('laravel-xero-timesheet-sync.timesheet_model');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-xero-timesheet-sync.php', 'laravel-xero-timesheet-sync');

        $this->app->bind(XeroTimesheetSyncPayrollService::class, function () {
            return new XeroTimesheetSyncPayrollService(resolve(Application::class));
        });
    }

    /**
     * Setup the resource publishing groups for Dcodegroup Xero Timesheets.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        $this->publishes([__DIR__ . '/../config/laravel-xero-timesheet-sync.php' => config_path('laravel-xero-timesheet-sync.php')], 'laravel-xero-timesheet-sync-config');
    }
}
