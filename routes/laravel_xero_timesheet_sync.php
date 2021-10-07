<?php

use Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers\XeroTimesheetPreviewController;
use Dcodegroup\LaravelXeroTimesheetSync\Http\Controllers\XeroTimesheetSummaryController;
use Illuminate\Support\Facades\Route;

Route::get('preview', XeroTimesheetPreviewController::class)->name('preview');
Route::get('summary', XeroTimesheetSummaryController::class)->name('summary');
