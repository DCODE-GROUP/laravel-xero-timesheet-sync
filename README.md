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

You should add the trait to your Timesheet::class ````