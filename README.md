# Laravel Xero Timesheet Sync

This package provides the standard xero functionality for syncing timesheets from your app to Xero.

## Installation

You can install the package via composer:

```bash
composer require dcodegroup/laravel-xero-timesheet-sync
```

Then run the install command.

```bash
php artsian laravel-xero-timesheet-sync:install
```

This will publish the configuration file and the migrations.

Then run the migrations

```bash
php artsian migrate
```

This will add the following fields to timesheets table and create two new tables.

```yaml
timesheets
---
can_include_in_xero_sync tinyint(1) DEFAULT=0
units double(8,2)
xero_timesheet_id unsignedbigint(255) FK >- xero_timesheets.id

xero_timesheets
---
id bigint(20) PK IDENTITY
xerotimeable_type varchar(255)
xerotimeable_id unsignedbigint
xero_timesheet_guid varchar(50) NULL # The identifier returned from xero
xero_employee_id varchar(50) NULL # may be redundant becuase its on the user that should be the polymporphic field. But saves a lookup
status varchar(50) NULL DEFAULT=DRAFT
start_date date
stop_date date
hours double(8,2)
synced_at timestamp NULL
deleted_at timestamp NULL
created_at timestamp NULL
updated_at timestamp NULL

xero_timesheets_lines
---
id bigint(20) PK IDENTITY
xero_timesheet_id unsignedbigint(255) FK >- xero_timesheets.id
xero-earnings_rate_id
date date
units double(8,2)
units_override double(8,2)
deleted_at timestamp NULL
created_at timestamp NULL
updated_at timestamp NULL

```

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file at `config/laravel-xero-timesheet-sync.php` and adjust as needed

You will need to add this field to your fillable array within the Timesheet model. Will not need this if you are extending the base timesheet model as that has guarded [].

```php
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
                   'can_include_in_xero_sync',
                   'units',
                   'xero_timesheet_id'
                   ...
    ];

```

It is suggested that you also cast the `can_include_in_xero_sync` as a boolean field in the `Timesheet` model.

```php
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    //protected $casts = [
    //               'can_include_in_xero_sync' => 'boolean',
    //               ...
    //];
    
    /**
      *  Merge casts with the existing
     */
    public function getCasts(): array
    {
        return parent::getCasts() + [
            'can_include_in_xero_sync' => 'boolean',
        ];
    }

```

You should add the interface to the `Timesheet::class` model.

```php

use Dcodegroup\LaravelXeroTimesheetSync\Contracts\SyncsTimesheetsToXero;

class Timesheet extends BaseTimesheet implements SyncsTimesheetsToXero
{

```

You should add the following trait to the Timesheet model.

```php
class Timesheet extends Authenticatable
{
    use XeroTimesheetable;

```


you need to implement these methods

In order for the timesheet row to be used / factored into sending to Xero the `timesheets.can_include_in_xero_sync` needs to be flagged / set as true or value 1. 
This needs to be implemented at the local app level. 

Three helper methods are provided in the `XeroTimesheetable.php` trait.

```php
    public function includeInXeroSync()
    {
        $this->update(['can_include_in_xero_sync', true]);
    }

    public function excludeFromXeroSync()
    {
        $this->update(['can_include_in_xero_sync', false]);
    }

    public function toggleIncludeInXeroSync()
    {
        $this->can_include_in_xero_sync = !$this->can_include_in_xero_sync;
        $this->save();
    }
```

## Routes

The following routes are exposed by the package

```
+--------+----------+-----------------------------+-----------------------------+-------------------------------------------------------------------------------------+----------------------------------+
| Domain | Method   | URI                         | Name                        | Action                                                                              | Middleware                       |
+--------+----------+-----------------------------+-----------------------------+-------------------------------------------------------------------------------------+----------------------------------+
|        | GET|HEAD | xero-timesheet-sync/preview | xero_timesheet_sync.preview | Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers\XeroTimesheetPreviewController | web                              |
|        |          |                             |                             |                                                                                     | App\Http\Middleware\Authenticate |
|        | GET|HEAD | xero-timesheet-sync/summary | xero_timesheet_sync.summary | Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers\XeroTimesheetSummaryController | web                              |
|        |          |                             |                             |                                                                                     | App\Http\Middleware\Authenticate |
+--------+----------+-----------------------------+-----------------------------+-------------------------------------------------------------------------------------+----------------------------------+


```

The package exposes some routes that allow you preview timesheets for a user

You can also view a summary of the timesheets for a period

## Jobs

## Commands

There is a command you can run to update the Xero configurations from `dcodegroup/laravel-xero-payroll-au` via Laravels scheduler

```bash
php artsian laravel-xero-timesheet-sync:update-xero-configuration-data
```

You should add it to your `app/Console/Kernel.php` file to run it once a day. You could run it more often if wanted with the --force flag

```php
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('laravel-xero-timesheet-sync:update-xero-configuration-data')->daily();    
        ...
    }

```