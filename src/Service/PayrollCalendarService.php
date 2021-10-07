<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Service;

use Carbon\Carbon;
use Dcodegroup\LaravelConfiguration\Models\Configuration;
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

        $calendar = collect($this->configurationPayrollCalendars)->first(function ($value, $key) use ($payrollCalendarId) {
            return data_get($value, 'PayrollCalendarID') == $payrollCalendarId;
        });

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

    private function getReferenceDate(array $calendar): Carbon
    {
        return Carbon::parse(data_get($calendar, 'ReferenceDate'));
    }

    private function getNextPaymentDate(array $calendar): string
    {
        //return data_get($calendar, 'PaymentDate');
        return now()->addMonth()->format('Y-m-d');
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
