<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Models;

use Dcodegroup\LaravelXeroTimesheetSync\Jobs\SendTimesheetToXero;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use XeroPHP\Models\PayrollAU\Timesheet\TimesheetLine;

class XeroTimesheet extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function xerotimeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function lines(): HasMany
    {
        return $this->hasMany(XeroTimesheetLine::class);
    }

    public function hasXeroGuid(): bool
    {
        return ! empty($this->xero_timesheet_guid);
    }

    public function prepareTimesheetLines(): array
    {
        return $this->lines()->get()->groupBy('earnings_rate_configuration_key')->map(function ($earningRate) {
            $line = new TimesheetLine();
            $line->setEarningsRateID($earningRate->first()->xero_earnings_rate_id);
            $line->setTrackingItemID($earningRate->first()->xero_tracking_item_id);

            $earningRate->sortBy('date')->each(function ($item) use ($line) {
                $line->addNumberOfUnit(number_format((float) $item->units_override, 2, '.', ''));
            });

            return ['TimesheetLine' => $line];
        })->values()->toArray();
    }

    public function updateLines(Request $request)
    {
        $this->lines()->get()->each(function ($line) use ($request) {
            $line->update(['units_override' => $request->input('xero_timesheet_line_id_'.$line->id)]);
        });

        SendTimesheetToXero::dispatch($this->fresh());
    }
}
