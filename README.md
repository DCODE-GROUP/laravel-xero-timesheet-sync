# Laravel Xero Timesheets

This package provides the standard xero functionality for syncing timesheets from your app to Xero.

## Installation

You can install the package via composer:

```bash
composer require dcodegroup/laravel-xero-timesheets
```

Then run the install command.

```bash
php artsian laravel-xero-timesheets:install
```

This will publish the configuration file and the migration file.

Run the migrations

```bash
php artsian migrate
```

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file at `config/laravel-xero-timesheets.php` and adjust as needed


## Usage

The package provides an endpoints which you can use. See the full list by running
```bash
php artsian route:list --name=xero
```

They are

[example.com/xero] Which is where you will generate the link to authorise xero. This is by default protected auth middleware but you can modify in the configuration. This is where you want to link to in your admin and possibly a new window

[example.com/xero/callback] This is the route for which xero will redirect back tp after the oauth has occurred. This is excluded from the middleware auth. You can change this list in the configuration also.

## BaseXeroService

The package has a `BaseXeroService` class located at `Dcodegroup\LaravelXeroOauth\BaseXeroService`

So your application should have its own XeroService extend this base class as the initialisation is already done.

```php
<?php

namespace App\Services\Xero;

use Dcodegroup\LaravelXeroOauth\BaseXeroService;
use XeroPHP\Models\Accounting\Contact;

class XeroService extends BaseXeroService
{
    /**
     * @inheritDoc
     */
    public function createContact(object $data)
    {
    
        /**
         * $this->>xeroClient is inherited from the  BaseXeroService
         */
        $contact = new Contact($this->xeroClient);

        $contact->setName($data->name . ' (' . $data->code . ')')
            ->setFirstName($data->name)
            ->setContactNumber($data->code)
            ->setAccountNumber($data->code)
            ->setContactStatus(Contact::CONTACT_STATUS_ACTIVE)
            ->setEmailAddress($data->email)
            ->setTaxNumber('ABN')
            ->setDefaultCurrency('AUD');

        $contact = head($contact->save()->getElements());

        return $this->xeroClient->loadByGUID(Contact::class, $contact['ContactID']);
    }

}
```