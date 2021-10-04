<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanIncludeInXeroSyncToTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timehseets', function (Blueprint $table) {
            $table->after('stop', function ($table) {
                $table->boolean('can_include_in_xero_sync')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropColumn('can_include_in_xero_sync');
        });
    }
}