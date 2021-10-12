<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Observers;

use Illuminate\Database\Eloquent\Model;

class LaravelXeroTimesheetLineSyncObserver
{
    public function updated(Model $model)
    {
        $model->timesheet->touch();
    }
}
