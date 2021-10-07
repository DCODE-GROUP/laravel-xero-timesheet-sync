<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Service;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Dcodegroup\LaravelConfiguration\Models\Configuration;

class PayrollCalendarService
{
    public array $configurationPayrollCalendars;

    public function __construct()
    {
        $this->configurationPayrollCalendars = Configuration::byKey('xero_payroll_calendars')
            ->get()
            ->pluck('value')
            ->first();
    }

    public function generatePeriods(string $payrollCalendarId = null): array
    {
        if (is_null($payrollCalendarId)) {
            return [];
        }

        $calendar = collect($this->configurationPayrollCalendars)->first(function ($value, $key)  use($payrollCalendarId) {
            return data_get($value, 'PayrollCalendarID') == $payrollCalendarId;
        });
        //dd($calendar);

        $referenceDate = $this->getReferenceDate($calendar);

        $period = CarbonPeriod::create()


    }

    public function getPayrollCalendarsFromConfiguration()
    {
        return $this->configurationPayrollCalendars;
    }

    private function getCalendarType(array $calendar): string
    {
        return data_get($calendar, 'CalendarType');
    }

    private function getReferenceDate(array $calendar): string
    {
        return data_get($calendar, 'ReferenceDate');
    }

    private function getNextPaymentDate(array $calendar): string
    {
        return data_get($calendar, 'getNextPaymentDate');
    }
}
