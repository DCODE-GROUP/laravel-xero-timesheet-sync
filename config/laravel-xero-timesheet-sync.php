<?php

return [
    /*
    * --------------------------------------------------------------------------
    * Laravel Xero Timesheet Sync AU Job Queue
    * --------------------------------------------------------------------------
    *
    * This will allow you to configure queue to use for background jobs
    *
    */

    'queue_name' => env('LARAVEL_XERO_TIMESHEET_QUEUE_NAME', 'default'),

    /*
     * The assumption is this will be the model used for timesheets.
     * You should update this to match your timesheet model. Should be this
     */
    'timesheet_model' => App\Models\Timesheet::class,

    /*
     * The name of the base layout to wrap the pages in.
     * The exposed routes will have to know the layout of the app in order to
     * Appear to look like the rest of the site.
     */

    'admin_app_layout' => env('LARAVEL_XERO_TIMESHEET_SYNC_ADMIN_APP_LAYOUT', 'layouts.admin'),

    /*
    * --------------------------------------------------------------------------
    * Laravel Xero Timesheet Sync Path
    * --------------------------------------------------------------------------
    *
    * This is the URI path where Laravel Xero Timesheet Sync will be accessible from.
    * Feel free to change this path to anything you like.
    *
    */

    'path' => env('LARAVEL_XERO_TIMESHEET_SYNC_PATH', 'xero-timesheet-sync'),

    /*
    * --------------------------------------------------------------------------
    * Laravel Xero Timesheet Sync AS
    * --------------------------------------------------------------------------
    *
    * This is the URI name prefix Laravel Xero Timesheet Sync will use in routes.
    * Feel free to change this path to anything you like.
    *
    */

    'as' => env('LARAVEL_XERO_TIMESHEET_SYNC_AS', 'xero_timesheet_sync'),

    /*
    * --------------------------------------------------------------------------
    * Laravel Xero Timesheet sync Route Middleware
    * --------------------------------------------------------------------------
    *
    * These middleware will get attached onto each Laravel Xero Timesheet Sync route, giving you
    * the chance to add your own middleware to this list or change any of
    * the existing middleware. Or, you can simply stick with this list.
    *
    */

    'middleware' => [
        'web',
        'auth',
    ],
];
