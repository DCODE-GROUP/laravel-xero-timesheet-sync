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
            $table->string('earnings_rate_configuration_key');
            $table->string('xero_earnings_rate_id', 50);
            $table->string('xero_tracking_item_id', 50)->nullable()->comment('rarely used but just in case its needed');
            $table->date('date');
            $table->double('units', 8, 2);
            $table->double('units_override', 8, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xero_timesheet_lines');
    }
}
