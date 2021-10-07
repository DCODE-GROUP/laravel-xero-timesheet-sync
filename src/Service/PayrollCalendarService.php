<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Service;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
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

    public function generatePeriods(string $payrollCalendarId = null): array
    {
        if (is_null($payrollCalendarId)) {
            return [];
        }

        $calendar = collect($this->configurationPayrollCalendars)->first(function ($value, $key) use ($payrollCalendarId) {
            return data_get($value, 'PayrollCalendarID') == $payrollCalendarId;
        });
        //dd($calendar);

        $referenceDate = $this->getReferenceDate($calendar);

        //$period = CarbonPeriod::create($this->getReferenceDate($calendar), $this->{"getInterval".$this->getCalendarType($calendar)}(), $this->getNextPaymentDate($calendar));
        $periods = $this->buildPeriods($calendar);

        //$periods = call_user_func($this->getReferenceDate($calendar), $this->getInterval($calendar));
        //dd($this->getReferenceDate($calendar));
        //$periods = $this->getReferenceDate($calendar)->weeksUntil($this->getNextPaymentDate($calendar));

        //dd(CarbonInterval::months(3));
        dump($periods);

        foreach ($periods as $period) {
            dump('period', $period);
            //dump('start:', $period->getStartDate());
            //dump('end:', $period->getendDate());
        }

        return [];
    }

    public function getPayrollCalendarsFromConfiguration()
    {
        return $this->configurationPayrollCalendars;
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

    private function buildPeriods($calendar)
    {
        $date = $this->getReferenceDate($calendar);

        switch ($this->getCalendarType($calendar)) {
            case PayrollCalendar::CALENDARTYPE_WEEKLY:
                return call_user_func_array([
                    $date,
                    'weeksUntil',
                ], [$this->getNextPaymentDate($calendar)]);

                //return $date->range($this->getNextPaymentDate($calendar), );
                //return

            case PayrollCalendar::CALENDARTYPE_FORTNIGHTLY:
            case PayrollCalendar::CALENDARTYPE_TWICEMONTHLY:
                return call_user_func_array([
                    $date,
                    'daysUntil',
                ], [
                    $this->getNextPaymentDate($calendar),
                    14,
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
