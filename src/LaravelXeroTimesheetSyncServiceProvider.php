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

        $this->app->bind(XeroTimesheetSyncService::class, function () {
            return new XeroTimesheetSyncService(resolve(Application::class));
        });
    }

    /**
     * Setup the resource publishing groups for Dcodegroup Xero Timesheets.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        //if (! Schema::hasTable('xero_tokens') && ! class_exists('CreateXeroTokensTable')) {
        //    $timestamp = date('Y_m_d_His', time());
        //
        //    $this->publishes([
        //                         __DIR__ . '/../database/migrations/create_xero_tokens_table.php.stub.php' => database_path('migrations/' . $timestamp . '_create_xero_tokens_table.php'),
        //                     ], 'laravel-xero-oauth-migrations');
        //}

        $this->publishes([__DIR__ . '/../config/laravel-xero-timesheet-sync.php' => config_path('laravel-xero-timesheet-sync.php')], 'laravel-xero-timesheet-sync-config');
    }
}
