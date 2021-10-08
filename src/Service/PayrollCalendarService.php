<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Service;

use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Dcodegroup\LaravelConfiguration\Models\Configuration;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheetLine;
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

    public function generatePeriodDays(string $payrollCalendarPeriod = null)
    {
        if (is_null($payrollCalendarPeriod)) {
            return [];
        }

        [
            $startDate,
            $endDate,
        ] = explode('||', $payrollCalendarPeriod);

        return $this->periodDayGenerator($startDate, $endDate);
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

    public function getXeroEarningRates(): array
    {
        $configurations = Configuration::byGroup('xero_payroll_au')->get();

        return [
            'xero_default_ordinary_earnings_rate_id' => [
                'name' => $configurations->where('key', 'xero_default_ordinary_earnings_rate_id')->pluck('name')->first(),
                'value' => $configurations->where('key', 'xero_default_ordinary_earnings_rate_id')->pluck('value')->first(),
            ],
            'xero_default_time_and_a_half' => [
                'name' => $configurations->where('key', 'xero_default_time_and_a_half')->pluck('name')->first(),
                'value' => $configurations->where('key', 'xero_default_time_and_a_half')->pluck('value')->first(),
            ],
            'xero_default_double_time' => [
                'name' => $configurations->where('key', 'xero_default_double_time')->pluck('name')->first(),
                'value' => $configurations->where('key', 'xero_default_double_time')->pluck('value')->first(),
            ],
        ];
    }

    public function getCalendarName(string $payrollCalendarId = null): string
    {
        if (is_null($payrollCalendarId)) {
            return '';
        }

        return $this->getName($this->getCalendar($payrollCalendarId));
    }

    public function retrieveUserTimeSheets(string $startDate, string $endDate, User $user): array
    {
        return Timesheet::query()
            ->whereDate('start', '>=', Carbon::parse($startDate)->startOfDay())
            ->whereDate('stop', '<=', Carbon::parse($endDate)->endOfDay())
            ->whereHasMorph('timesheetable', [User::class], fn (Builder $builder) => $builder->where('id', $user->id))
            ->get()
            ->map(function ($timesheet) {
                if ($timesheet->start->toDateString() != $timesheet->stop->toDateString()) {
                    return [
                        [
                            // before midnight
                            'date' => $timesheet->start->toDateString(),
                            'units' => round($timesheet->start->floatDiffInHours($timesheet->start->copy()
                                ->endOfDay()
                                ->addSecond()), 2),
                        ],
                        [
                            // after midnight
                            'date' => $timesheet->stop->toDateString(),
                            'units' => round($timesheet->stop->floatDiffInHours($timesheet->stop->copy()
                                ->startOfDay()), 2),
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
            })
            ->flatten(1)
            ->groupBy('date')
            ->mapWithKeys(function ($item, $key) {
                return [
                    $key => [
                        'units' => $item->sum('units'),
                    ],
                ];
            })
            ->toArray()
        ;
    }

    public function findOrderCreateXeroTimesheet(string $payrollCalendarPeriod = null, int $userId = null)
    {
        if (is_null($payrollCalendarPeriod) || is_null($userId)) {
            return false;
        }

        $user = User::find($userId);

        if (!$user instanceof User) {
            return false;
        }

        [
            $startDate,
            $endDate,
        ] = explode('||', $payrollCalendarPeriod);

        $model = XeroTimesheet::query()
            ->whereDate('start_date', '>=', $startDate)
            ->whereDate('end_date', '<=', $endDate)
            ->whereHasMorph('xerotimeable', [User::class], fn (Builder $builder) => $builder->where('id', $user->id))
            ->first()
        ;

        if ($model instanceof XeroTimesheet) {
            return $model;
        }

        return $this->generateDraftTimesheet($startDate, $endDate, $user);
    }

    private function periodDayGenerator(string $startDate, string $endDate): array
    {
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);

        $days = [];

        foreach ($period as $item) {
            $days[$item->toDateString()] = $item->format('D jS M');
        }

        return $days;
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

    private function generateDraftTimesheet(string $startDate, string $endDate, User $user): XeroTimesheet
    {
        $xeroTimesheet = $user->xerotimeable()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'xero_employee_id' => $user->xero_employee_id,
        ]);

        $this->generateInitialTimesheetRows($xeroTimesheet, $startDate, $endDate, $user);

        return $xeroTimesheet;
    }

    private function generateInitialTimesheetRows(XeroTimesheet $xeroTimesheet, string $startDate, string $endDate, User $user)
    {
        $days = $this->periodDayGenerator($startDate, $endDate);
        $earningRates = $this->getXeroEarningRates();
        $timesheets = $this->retrieveUserTimeSheets($startDate, $endDate, $user);

        foreach ($earningRates as $rate) {
            foreach ($days as $key => $label) {
                XeroTimesheetLine::create([
                    'xero_timesheet_id' => $xeroTimesheet->id,
                    'xero_earnings_rate_id' => $rate['value'],
                    'date' => $key,
                    'units' => $timesheets[$key]['units'] ?? 0,
                    'units_override' => $timesheets[$key]['units'] ?? 0,
                ]);
            }
        }
    }
}
