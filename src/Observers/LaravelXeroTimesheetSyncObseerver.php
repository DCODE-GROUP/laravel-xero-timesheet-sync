<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Observers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LaravelXeroTimesheetSyncObseerver
{
    public function saving(Model $model)
    {
        if ($model->start instanceof Carbon && $model->stop instanceof Carbon) {
            $model->units = $model->start->diffInHours($model->stop);
        }
    }
}
