<?php

use Dcodegroup\LaravelXeroTimesheetSync\Models\XeroTimesheet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXeroTimesheetLinesTable extends Migration
{
        public function up()
        {
            Schema::create('xero_timesheet_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(XeroTimesheet::class);
                $table->string('xero_employee_id', 50)->nullable();
                $table->string('status', 50)->nullable();
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->double('8,2');

                $table->softDeletes();
                $table->timestamps();
            });
        }

        public function down()
        {
            Schema::dropIfExists('xero_timesheets');
        }
}