<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Observers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LaravelTimesheetObserver
{
    public function saving(Model $model)
    {
        if ($model->start instanceof Carbon && $model->stop instanceof Carbon) {
            $model->units = round($model->start->floatDiffInHours($model->stop), 2);
        }
    }
}
