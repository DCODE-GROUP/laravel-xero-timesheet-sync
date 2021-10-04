<?php

namespace Dcodegroup\LaravelXeroTimesheetSync;

use Illuminate\Support\Facades\Schema;
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
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-xero-timesheet-sync.php', 'laravel-xero-timesheet-sync');

        $this->app->bind(BaseXeroTimesheetSyncService::class, function () {
            return new BaseXeroTimesheetSyncService(resolve(Application::class));
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

        if (Schema::hasTable('timehsheets') &&
            ! Schema::hasColumn('timehsheets', 'can_include_in_xero_sync')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                                 __DIR__ . '/../database/migrations/add_can_include_in_xero_sync_to_timesheets_table.stub.php' => database_path('migrations/' . $timestamp . '_add_can_include_in_xero_sync_to_timesheets_table.php'),
                             ], 'laravel-xero-timesheet-sync-timesheet-table-migrations');
        }
    }
}
