<?php

namespace Dcodegroup\LaravelXeroTimesheetSync;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Dcodegroup\LaravelXeroTimesheetSync\Commands\AutoUpdateXeroConfigurationData;
use Dcodegroup\LaravelXeroTimesheetSync\Commands\InstallCommand;
use Dcodegroup\LaravelXeroTimesheetSync\Observers\LaravelTimesheetObserver;
use Dcodegroup\LaravelXeroTimesheetSync\Observers\LaravelXeroTimesheetLineSyncObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use XeroPHP\Application;

class LaravelXeroTimesheetSyncServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->offerPublishing();
        $this->registerCommands();

        $timesheetClass = config('laravel-xero-timesheet-sync.timesheet_model');
        $timesheetClass::observe(new LaravelTimesheetObserver());

        $timesheetLineClass = config('laravel-xero-timesheet-sync.xero_timesheet_line_model');
        $timesheetLineClass::observe(new LaravelXeroTimesheetLineSyncObserver());

        $this->registerResources();
        $this->registerRoutes();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-xero-timesheet-sync.php', 'laravel-xero-timesheet-sync');

        $this->app->bind(BaseXeroTimesheetSyncService::class, function () {
            return new BaseXeroTimesheetSyncService(resolve(Application::class));
        });

        $this->registerCarbonMacros();
    }

    /**
     * Setup the resource publishing groups for Dcodegroup Xero Timesheets.
     */
    protected function offerPublishing()
    {
        $this->publishes([__DIR__.'/../config/laravel-xero-timesheet-sync.php' => config_path('laravel-xero-timesheet-sync.php')], 'laravel-xero-timesheet-sync-config');

        if (Schema::hasTable('timesheets')
            && ! Schema::hasColumns('timesheets', [
                'can_include_in_xero_sync',
                'units',
                'xero_timesheet_id',
            ])) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/add_laravel_timesheet_sync_fields_to_timesheets_table.stub.php' => database_path('migrations/'.$timestamp.'_add_laravel_timesheet_sync_fields_to_timesheets_table.php'),
            ], 'laravel-xero-timesheet-sync-timesheet-table-migrations');
        }

        if (! Schema::hasTable('xero_timesheets')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_xero_timesheets_table.stub.php' => database_path('migrations/'.$timestamp.'_create_xero_timesheets_table.php'),
            ], 'laravel-xero-timesheet-sync-timesheet-table-migrations');
        }

        if (! Schema::hasTable('xero_timesheet_lines')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_xero_timesheet_lines_table.stub.php' => database_path('migrations/'.$timestamp.'_create_xero_timesheet_lines_table.php'),
            ], 'laravel-xero-timesheet-sync-timesheet-table-migrations');
        }
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                AutoUpdateXeroConfigurationData::class,
            ]);
        }
    }

    protected function registerResources()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'xero-timesheet-sync-translations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'xero-timesheet-sync-views');
    }

    protected function registerRoutes()
    {
        Route::group([
            'prefix' => config('laravel-xero-timesheet-sync.path'),
            'as' => Str::slug(config('laravel-xero-timesheet-sync.as'), '_').'.',
            'middleware' => config('laravel-xero-timesheet-sync.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/laravel_xero_timesheet_sync.php');
        });
    }

    public function registerCarbonMacros()
    {
        Carbon::macro('addFortnight', function () {
            return $this->addWeeks(2);
        });

        Carbon::macro('fortnightUntil', function ($date) {
            return CarbonPeriod::create($this, '14 days', $date);
        });
    }
}
