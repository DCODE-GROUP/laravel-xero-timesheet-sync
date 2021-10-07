<?php

namespace Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class XeroTimesheetSummaryController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('xero-timesheet-sync-views::summary');
    }
}
