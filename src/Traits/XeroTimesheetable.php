<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Traits;

use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait XeroTimesheetable
{

    public function xeroTimesheetable(): MorphTo
    {
        return $this->morphOne(XeroTimesheet::class, 'xerotransformable');
    }

}
