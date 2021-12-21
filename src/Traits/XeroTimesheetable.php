<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Traits;

use App\Models\User;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait XeroTimesheetable
{
    public function xerotimeable(): MorphOne
    {
        return $this->morphOne(XeroTimesheet::class, 'xerotimeable');
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

    public function scopeEligibleForXero(Builder $query): Builder
    {
        return $query->where('can_include_in_xero_sync', true);
    }

    public function toggleIncludeInXeroSync()
    {
        $this->can_include_in_xero_sync = ! $this->can_include_in_xero_sync;
        $this->save();

        $this->updateTimesheetLines();
    }

    private function updateTimesheetLines()
    {
        $model = XeroTimesheet::query()->periodBetween($this->start?->toDateString(), $this->stop?->toDateString())
                              ->whereHasMorph('xerotimeable', [User::class], fn (Builder $builder) => $builder->where('id', $this->timesheetable_id))
                              ->first();

        if ($model instanceof XeroTimesheet) {
            if ($this->start?->toDateString() != $this->stop?->toDateString()) {
                /*
                 * check if the timesheet spans over two days
                 * If it is then split the period
                 */

                $startLine = $model->lines()->whereDate('date', $this->start?->toDateString())->first();
                $endLine = $model->lines()->whereDate('date', $this->stop?->toDateString())->first();

                if ($this->canSendToXero()) {
                    $startLine->update(['units' => round($this->start->floatDiffInHours($this->start->copy()
                                                                                                    ->endOfDay()
                                                                                                    ->addSecond()), 2)]);

                    $endLine->update(['units' => round($this->stop->floatDiffInHours($this->stop->copy()
                                                                                                ->startOfDay()), 2)]);
                } else {
                    $startLine->update(['units' => 0]);
                    $endLine->update(['units' => 0]);
                }

                $startLine->timesheet()->touch();
            } else {
                $line = $model->lines()->whereDate('date', $this->start?->toDateString())->first();

                if ($this->canSendToXero()) {
                    $line->update(['units' => $this->units]);
                } else {
                    $line->update(['units' => 0]);
                }
            }
        }
    }
}
