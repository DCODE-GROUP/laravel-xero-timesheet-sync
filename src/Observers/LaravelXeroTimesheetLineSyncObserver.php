<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Observers;

use Illuminate\Database\Eloquent\Model;

class LaravelXeroTimesheetLineSyncObserver
{
    public function updated(Model $model)
    {
        dd($model);
        $model->timesheet->touch();
    }
}
