<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Xero Payroll AU Job Queue
    |--------------------------------------------------------------------------
    |
    | This will allow you to configure queue to use for background jobs
    |
    */

    'queue_name' => env('LARAVEL_XERO_TIMESHEET_QUEUE_NAME', 'default'),

    /*
    |
    | The assumption is this will be the model used for timesheets. You are free to update this.
    |
    */
    'timesheet_model' => App\Models\Timesheet::class,
];
