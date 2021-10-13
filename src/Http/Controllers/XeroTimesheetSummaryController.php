<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Dcodegroup\LaravelXeroTimesheetSync\Service\PayrollCalendarService;
use Illuminate\Http\Request;

class XeroTimesheetSummaryController extends Controller
{
    protected PayrollCalendarService $service;

    public function __construct(PayrollCalendarService $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request)
    {
        return view('xero-timesheet-sync-views::summary')
            ->with('xeroPayrollCalendars', $this->service->getPayrollCalendarsFromConfiguration())
            ->with('payrollCalendarPeriods', $this->service->generateCalendarPeriods($request->input('payroll_calendar')))
            ->with('payrollCalendarPeriodDays', $this->service->generatePeriodDays($request->input('payroll_calendar_period')))
            ->with('calendarName', $this->service->getCalendarName($request->input('payroll_calendar')))
            ->with('xeroTimesheets', $this->retrieveUserTimesheets($request))
            ->with('earningRates', $this->service->getXeroEarningRates())
            ;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed
     */
    private function retrieveUserTimesheets(Request $request)
    {
        if (! $request->filled('payroll_calendar_period')) {
            return collect([]);
        }

        [
            $startDate,
            $endDate,
        ] = explode('||', $request->input('payroll_calendar_period'));

        return XeroTimesheet::query()->periodBetween($startDate, $endDate)->get();
    }
}
