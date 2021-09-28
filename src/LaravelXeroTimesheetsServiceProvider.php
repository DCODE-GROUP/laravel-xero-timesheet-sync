<?php

namespace Dcodegroup\LaravelXeroTimesheets;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use XeroPHP\Application;

class LaravelXeroTimesheetsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->offerPublishing();
        $this->registerResources();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-xero-timesheets.php', 'laravel-xero-timesheets');

        $this->app->bind(XeroTimesheetService::class, function () {
            return new XeroTimesheetService(resolve(Application::class));
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

        $this->publishes([__DIR__ . '/../config/laravel-xero-timesheets.php' => config_path('laravel-xero-timesheets.php')], 'laravel-xero-timesheets-config');
    }
}
