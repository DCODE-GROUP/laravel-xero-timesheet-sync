<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Dcodegroup\LaravelXeroTimesheetSync\Service\PayrollCalendarService;
use Illuminate\Http\Request;

class XeroTimesheetPreviewController extends Controller
{
    protected PayrollCalendarService $service;

    public function __construct(PayrollCalendarService $service)
    {
        $this->service = $service;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request)
    {
        return view('xero-timesheet-sync-views::preview')
            ->with('users', User::all()->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->xero_employee_name,
                ];
            }))
            ->with('xero_payroll_calendars', $this->service->getPayrollCalendarsFromConfiguration())
            ->with('xero_timesheet', new XeroTimesheet)
            ->with('timesheets', $this->service->retrieveUserTimeSheets($request->input('payroll_calendar_period'), $request->input('user_id')))
            ->with('payroll_calendar_periods', $this->service->generateCalendarPeriods($request->input('payroll_calendar')))
            //->with('payroll_calendar_period', $request->input('payroll_calendar_period'))
            ->with('displayPreview', $this->displayPreview($request))
            ->with('calendarName', $this->service->getCalendarName($request->input('payroll_calendar')));
    }

    protected function displayPreview(Request $request): bool
    {
        return $request->filled('user_id') && $request->filled('payroll_calendar') && $request->filled('payroll_calendar_period');
    }
}
