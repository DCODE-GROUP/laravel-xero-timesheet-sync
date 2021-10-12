<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        //$users = User::hasXeroEmployeeId()->get()->map(function ($item) use ($request) {
        //    return [
        //        'value' => $item->id,
        //        'label' => $item->xero_employee_name,
        //    ];
        //});

        /**
         * Need to work out if all xero_timesheets are generated for all users
         */
        $isTimesheetsGenerated = false;



        return view('xero-timesheet-sync-views::summary')
            //->with('users', $users)
            ->with('xeroPayrollCalendars', $this->service->getPayrollCalendarsFromConfiguration())
            ->with('payrollCalendarPeriods', $this->service->generateCalendarPeriods($request->input('payroll_calendar')))
            ->with('payrollCalendarPeriodDays', $this->service->generatePeriodDays($request->input('payroll_calendar_period')))
            ->with('calendarName', $this->service->getCalendarName($request->input('payroll_calendar')))
            //->with('earningRates', $this->service->getXeroEarningRates())
            //->with('xeroTimesheets', $xeroTimesheets)
            ->with('isTimesheetsGenerated', $isTimesheetsGenerated)
            //->with('xeroTimesheetLines', $xeroTimesheetLines)
            ;
    }
}
