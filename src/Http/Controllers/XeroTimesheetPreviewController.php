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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request)
    {
        $xeroTimesheet = $this->service->findOrderCreateXeroTimesheet($request->input('payroll_calendar_period'), $request->input('user_id'));
        $xeroTimesheetLines = collect([]);

        if ($xeroTimesheet instanceof XeroTimesheet) {
            $xeroTimesheetLines = $xeroTimesheet->lines()->get();
        }

        return view('xero-timesheet-sync-views::preview')
            ->with('users', User::hasXeroEmployeeId()->get()->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->xero_employee_name,
                ];
            }))
            ->with('xeroPayrollCalendars', $this->service->getPayrollCalendarsFromConfiguration())
            ->with('payrollCalendarPeriods', $this->service->generateCalendarPeriods($request->input('payroll_calendar')))
            ->with('payrollCalendarPeriodDays', $this->service->generatePeriodDays($request->input('payroll_calendar_period')))
            ->with('displayPreview', $this->displayPreview($request))
            ->with('calendarName', $this->service->getCalendarName($request->input('payroll_calendar')))
            ->with('xeroTimesheet', $xeroTimesheet)
            ->with('xeroTimesheetLines', $xeroTimesheetLines)
            ->with('earningRates', $this->service->getXeroEarningRates())
        ;
    }

    protected function displayPreview(Request $request): bool
    {
        return $request->filled('user_id') && $request->filled('payroll_calendar') && $request->filled('payroll_calendar_period');
    }
}
