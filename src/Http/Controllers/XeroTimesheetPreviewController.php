<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dcodegroup\LaravelXeroTimesheetSync\Http\Requests\XeroTimesheetPreviewRequest;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Dcodegroup\LaravelXeroTimesheetSync\Service\PayrollCalendarService;

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
    public function __invoke(XeroTimesheetPreviewRequest $request)
    {
        $xeroTimesheet = $this->service->findOrCreateXeroTimesheet($request->input('payroll_calendar_period'), $request->input('user_id'));
        $xeroTimesheetLines = collect([]);

        if ($xeroTimesheet instanceof XeroTimesheet) {
            $xeroTimesheetLines = $xeroTimesheet->lines()->get();
        }

        return view('xero-timesheet-sync-views::preview')
            ->with('users', User::hasXeroEmployeeId()->get()->map(function ($item) use ($request) {
                return [
                    'value' => $item->id,
                    'label' => $item->xero_employee_name,
                    'selected' => $request->input('user_id') == $item->id,
                ];
            }))
            ->with('xeroPayrollCalendars', $this->service->getPayrollCalendarsFromConfiguration())
            ->with('payrollCalendarPeriods', $this->service->generateCalendarPeriods($request->input('payroll_calendar')))
            ->with('payrollCalendarPeriodDays', $this->service->generatePeriodDays($request->input('payroll_calendar_period')))
            ->with('displayPreview', $this->displayPreview($request))
            ->with('calendarName', $this->service->getCalendarName($request->input('payroll_calendar')))
            ->with('xeroTimesheet', $xeroTimesheet)
            ->with('xeroTimesheetLines', $xeroTimesheetLines)
            ->with('earningRates', $this->service->getXeroEarningRates());
    }

    protected function displayPreview(XeroTimesheetPreviewRequest $request): bool
    {
        return $request->filled('user_id') && $request->filled('payroll_calendar') && $request->filled('payroll_calendar_period');
    }
}
