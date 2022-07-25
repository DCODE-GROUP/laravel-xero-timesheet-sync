<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Middleware;

use App\Models\User;
use Closure;
use Dcodegroup\LaravelXeroTimesheetSync\Jobs\GenerateTimesheetsForSummary;
use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            $userIdsWithTimesheets = XeroTimesheet::query()->periodBetween($startDate, $endDate)->userHasTimesheetForPeriod($users->pluck('id')->toArray())->get();

            if ($userIdsWithTimesheets->count() != $users->count()) {
                if (! Cache::has("laravel-timesheet-sync-summary-{$request->input('payroll_calendar')}-{$request->input('payroll_calendar_period')}")) {
                    GenerateTimesheetsForSummary::dispatch($users, $userIdsWithTimesheets, $request->input('payroll_calendar'), $request->input('payroll_calendar_period'));
                }

                return redirect()->route('xero_timesheet_sync.summary-generating', [
                    'payroll_calendar' => $request->input('payroll_calendar'),
                    'payroll_calendar_period' => $request->input('payroll_calendar_period'),
                ]);
            }
        }

        return $next($request);
    }
}
