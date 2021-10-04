<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Traits;

use Illuminate\Database\Eloquent\Relations\MorphTo;

trait XeroTimesheetable
{
    public function xeroTimesheetable(): MorphTo
    {
        return $this->morphTo();
    }
}
