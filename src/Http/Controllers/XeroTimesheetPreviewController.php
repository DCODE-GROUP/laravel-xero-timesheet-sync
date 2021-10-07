<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            ->with('payroll_calendar', $request->input('payroll_calendar'))
            ->with('payroll_calendar_periods', $this->service->generatePeriods($request->input('payroll_calendar')))
        ;
    }
}
