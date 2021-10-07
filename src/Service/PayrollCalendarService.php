<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Service;

use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Dcodegroup\LaravelConfiguration\Models\Configuration;
use Illuminate\Database\Eloquent\Builder;
use XeroPHP\Models\PayrollAU\PayrollCalendar;

class PayrollCalendarService
{
    public array $configurationPayrollCalendars;

    public function __construct()
    {
        $this->configurationPayrollCalendars = Configuration::byKey('xero_payroll_calendars')
            ->get()
            ->pluck('value')
            ->first()
        ;
    }

    public function generatePeriodDays($startDate, $endDate)
    {
    }

    public function generateCalendarPeriods(string $payrollCalendarId = null): array
    {
        if (is_null($payrollCalendarId)) {
            return [];
        }

        $calendar = $this->getCalendar($payrollCalendarId);

        $calendarPeriodStarts = $this->buildCalendarPeriodStartDates($calendar);

        return collect($calendarPeriodStarts)->map(function ($periodStart) use ($calendar) {
            $periodEnd = $periodStart->copy()->{$this->getMethodForCalendarType($calendar)}();

            return [
                'value' => $periodStart->toDateString().'||'.$periodEnd->toDateString(),
                'label' => $periodStart->format('j M Y').' '.$periodEnd->format('j M Y'),
            ];
        })->toArray();
    }

    public function getPayrollCalendarsFromConfiguration(): array
    {
        return $this->configurationPayrollCalendars;
    }

    public function getCalendarName(string $payrollCalendarId = null): string
    {
        if (is_null($payrollCalendarId)) {
            return '';
        }

        return $this->getName($this->getCalendar($payrollCalendarId));
    }

    public function retrieveUserTimeSheets(string $payrollCalendarPeriods = null, int $userId = null): array
    {
        if (is_null($payrollCalendarPeriods) || is_null($userId)) {
            return [];
        }

        $user = User::find($userId);

        //dd($payrollCalendarPeriods);
        [
            $tmpStartDate,
            $tmpEndDate,
        ] = explode('||', $payrollCalendarPeriods);

        $startDate = Carbon::parse($tmpStartDate)->startOfDay();
        $endDate = Carbon::parse($tmpEndDate)->endOfDay();

        return Timesheet::query()
            ->whereDate('start', '>=', $startDate)
            ->whereDate('stop', '<=', $endDate)
            ->whereHasMorph(
                'timesheetable',
                [User::class],
                fn (Builder $builder) => $builder->where('id', $user->id)
            )
            ->get()
            ->map(function ($timesheet) {
                if ($timesheet->start->toDateString() != $timesheet->stop->toDateString()) {
                    logger($timesheet->start->toDateTimeString());
                    logger($timesheet->start->copy()->endOfDay());
                    logger($timesheet->stop->copy()->startOfDay());
                    logger($timesheet->stop->toDateTimeString());

                    return [
                        [
                            // before midnight
                            'date' => $timesheet->start->toDateString(),
                            'units' => round($timesheet->start->floatDiffInHours($timesheet->start->copy()->endOfDay()->addSecond()), 2),
                        ],
                        [
                            // after midnight
                            'date' => $timesheet->stop->toDateString(),
                            'units' => round($timesheet->stop->floatDiffInHours($timesheet->stop->copy()->startOfDay()), 2),
                        ],
                    ];
                }

                return [
                    'date' => $timesheet->start->toDateString(),
                    'units' => $timesheet->units,
                ];
            })
            ->transform(function ($item) {
                if (array_key_exists('date', $item)) {
                    return [$item];
                }

                return $item;
            })->flatten(1)
            ->groupBy('date')
            ->mapWithKeys(function ($item, $key) {
                return [
                    $key => [
                        'units' => $item->sum('units'),
                    ],
                ];
            })->toArray();
    }

    private function getCalendar(string $payrollCalendarId)
    {
        return collect($this->configurationPayrollCalendars)->first(function ($value, $key) use ($payrollCalendarId) {
            return data_get($value, 'PayrollCalendarID') == $payrollCalendarId;
        });
    }

    private function getMethodForCalendarType(array $calendar): string
    {
        switch ($this->getCalendarType($calendar)) {
            case PayrollCalendar::CALENDARTYPE_WEEKLY:
                return 'addWeek';

            case PayrollCalendar::CALENDARTYPE_FORTNIGHTLY:
            case PayrollCalendar::CALENDARTYPE_TWICEMONTHLY:
                return 'addFortnight';

            case PayrollCalendar::CALENDARTYPE_MONTHLY:
            case PayrollCalendar::CALENDARTYPE_FOURWEEKLY:
                return 'addMonth';

            case PayrollCalendar::CALENDARTYPE_QUARTERLY:
                return 'addQuarter';

            default:
                return 'no-type';
        }
    }

    private function getCalendarType(array $calendar): string
    {
        return data_get($calendar, 'CalendarType');
    }

    private function getName(array $calendar): string
    {
        return data_get($calendar, 'Name') ?? '';
    }

    private function getReferenceDate(array $calendar): Carbon
    {
        return Carbon::parse(data_get($calendar, 'ReferenceDate'));
    }

    private function getNextPaymentDate(array $calendar): string
    {
        return data_get($calendar, 'PaymentDate');
        //return now()->addMonth()->format('Y-m-d');
    }

    private function buildCalendarPeriodStartDates($calendar)
    {
        $date = $this->getReferenceDate($calendar);
        //dd($this->getCalendarType($calendar));
        //dd($date);
        switch ($this->getCalendarType($calendar)) {
            case PayrollCalendar::CALENDARTYPE_WEEKLY:
                return call_user_func_array([
                    $date,
                    'weeksUntil',
                ], [$this->getNextPaymentDate($calendar)]);

            case PayrollCalendar::CALENDARTYPE_FORTNIGHTLY:
            case PayrollCalendar::CALENDARTYPE_TWICEMONTHLY:
                return call_user_func_array([
                    $date,
                    'fortnightUntil',
                ], [
                    $this->getNextPaymentDate($calendar),
                ]);

            case PayrollCalendar::CALENDARTYPE_MONTHLY:
            case PayrollCalendar::CALENDARTYPE_FOURWEEKLY:
                return call_user_func_array([
                    $date,
                    'monthsUntil',
                ], [$this->getNextPaymentDate($calendar)]);

            case PayrollCalendar::CALENDARTYPE_QUARTERLY:
                return call_user_func_array([
                    $date,
                    'quartersUntil',
                ], [$this->getNextPaymentDate($calendar)]);
        }
    }
}
