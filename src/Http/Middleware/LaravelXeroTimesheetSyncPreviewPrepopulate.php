<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class LaravelXeroTimesheetSyncPreviewPrepopulate
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->filled('user_id')) {
            $user = User::find($request->input('user_id'));

            if (! $request->filled('payroll_calendar')) {
                $request->request->set('payroll_calendar', $user->xero_default_payroll_calendar_id);
            }
        }

        return $next($request);
    }
}
