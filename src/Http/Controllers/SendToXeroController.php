<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use Dcodegroup\LaravelXeroTimesheetSync\Http\Requests\SendToXeroRequest;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;

class SendToXeroController extends Controller
{
    public function __invoke(SendToXeroRequest $request, XeroTimesheet $xeroTimesheet)
    {
        $xeroTimesheet->updateLines($request);

        return redirect()->route('xero_timesheet_sync.preview', ['user_id' => $request->input('user_id'),
            'payroll_calendar' => $request->input('payroll_calendar'),
            'payroll_calendar_period' => $request->input('payroll_calendar_period'),
        ])->with('statusMessage', __('xero-timesheet-sync-translations::laravel-xero-timesheet-sync.alerts.send_to_xero_queued'));
    }
}
