<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Traits;

use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait XeroTimesheetable
{
    public function xeroTimesheetable(): MorphOne
    {
        return $this->morphOne(XeroTimesheet::class, 'xerotransformable');
    }

    public function canSendToXero(): bool
    {
        return $this->can_include_in_xero_sync == true;
    }

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
}
