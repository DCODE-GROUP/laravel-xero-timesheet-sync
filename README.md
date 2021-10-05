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
xero_timesheetabe_type varchar(255)
xero_timesheetabe_id unsignedbigint
xero_employee_id varchar(50) NULL # may be redundant becuase its on the user that should be the polymporphic field. But saves a lookup
status varchar(50) NULL DEFAULT=DRAFT
start_date date
stop_date date
hours double(8,2)
deleted_at timestamp NULL
created_at timestamp NULL
updated_at timestamp NULL

xero_timesheets_lines
---
id bigint(20) PK IDENTITY
xero_timesheet_id NULL
date
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