<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Middleware;

use App\Models\User;
use Closure;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Http\Request;

class LaravelXeroTimesheetSyncSummaryCheckXeroTimesheetsExist
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->filled('payroll_calendar_period') && $request->filled('payroll_calendar')) {
            $users = User::hasXeroEmployeeId()->get();

            [
                $startDate,
                $endDate,
            ] = explode('||', $request->input('payroll_calendar_period'));

            /**
             * I AM MAKING AN ASSUMPTION HERE THAT THE PERIOD IS FOR A GIVEN CALENDAR.
             * TIMESHEETS IN ZERO HAVE NO KNOWLEDGE OR CARE ABOUT CALENDARS SO USING THE SAME
             * ASSUMPTION
             */

            $results = XeroTimesheet::query()->periodBetween($startDate, $endDate)->userHasTimesheetForPeriod($users->pluck('id')->toArray())->get();

            if (count($results) != $users->count()) {
                return redirect()->route('xero_timesheet_sync.summary-generating', [
                    'payroll_calendar' => $request->input('payroll_calendar'),
                    'payroll_calendar_period' => $request->input('payroll_calendar_period'),
                ]);
            }
        }

        return $next($request);
    }
}
