<?php

use Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers\SendToXeroController;
use Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers\XeroTimesheetPreviewController;
use Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers\XeroTimesheetSummaryController;
use Dcodegroup\LaravelXeroTimesheetSync\Http\Middleware\LaravelXeroTimesheetSyncPreviewPrepopulate;
use Dcodegroup\LaravelXeroTimesheetSync\Http\Middleware\LaravelXeroTimesheetSyncSummaryCheckXeroTimesheetsExist;
use Illuminate\Support\Facades\Route;

Route::get('preview', XeroTimesheetPreviewController::class)->name('preview')->middleware(LaravelXeroTimesheetSyncPreviewPrepopulate::class);
Route::get('summary', XeroTimesheetSummaryController::class)->name('summary')->middleware(LaravelXeroTimesheetSyncSummaryCheckXeroTimesheetsExist::class);
Route::post('send-to-xero/{xeroTimesheet}', SendToXeroController::class)->name('send-to-xero');
Route::view('summary-generating', 'xero-timesheet-sync-views::generating', [
    'payroll_calendar' => request('payroll_calendar'),
    'payroll_calendar_period' => request('payroll_calendar_period'),
])->name('summary-generating');
