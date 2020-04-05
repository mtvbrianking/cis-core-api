<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientVisitLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_visit_logs', function (Blueprint $table) {
            $table->string('id', 11);
            $table->string('visit_id', 11);
            $table->string('station_id', 11);
            $table->uuid('user_id');
            $table->timestamp('created_at');

            $table->primary('id');

            $table->foreign('visit_id')->references('id')->on('patient_visits')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('station_id')->references('id')->on('stations')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_visits', function (Blueprint $table) {
            $table->dropForeign(['visit_id']);
            $table->dropForeign(['station_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('patient_visit_logs');
    }
}
