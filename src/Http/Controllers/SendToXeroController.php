<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use Dcodegroup\LaravelXeroTimesheetSync\Service\PayrollCalendarService;
use Illuminate\Http\Request;

class SendToXeroController extends Controller
{
    protected PayrollCalendarService $service;

    public function __construct(PayrollCalendarService $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request)
    {

        // dispatch send to xero

        return response()->redirect('xero_timesheet_sync.preview ', ['user_id' => $request->input('user_id'),
                                                                     'payroll_calendar' => $request->input('payroll_calendar'),
                                                                     'payroll_calendar_period' => $request->input('payroll_calendar_period'),
        ]);
    }
}
