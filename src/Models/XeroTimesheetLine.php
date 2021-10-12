<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XeroTimesheetLine extends Model
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
        'date' => 'date',
        'units' => 'double',
        'units_override' => 'double',
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['timesheet'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['summary_form_key'];

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(XeroTimesheet::class);
    }

    public function getSummaryFormKeyAttribute(): string
    {
        return $this->earnings_rate_configuration_key.'_'.$this->date->toDateString();
    }
}
