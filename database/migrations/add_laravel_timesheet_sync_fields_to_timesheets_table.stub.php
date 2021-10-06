<?php

use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLaravelTimesheetSyncFieldsToTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->after('stop', function ($table) {
                $table->boolean('can_include_in_xero_sync')->default(false);
                $table->double('units', 8, 2);
                $table->foreignIdFor(XeroTimesheet::class)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropColumn([
                'can_include_in_xero_sync',
                'units',
                'xero_timesheet_id',
            ]);
        });
    }
}
