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

This will publish the configuration file.

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file at `config/laravel-xero-timesheet-sync.php` and adjust as needed

You will need to add this field to your fillable array within the Timesheet model.

```php
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
                   'can_include_in_xero_sync',
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
    protected $casts = [
                   'can_include_in_xero_sync' => 'boolean',
                   ...
    ];

```

You should add the following trait to the Timesheet model.

```php
class Timesheet extends Authenticatable
{
    use XeroTimesheetable;

```