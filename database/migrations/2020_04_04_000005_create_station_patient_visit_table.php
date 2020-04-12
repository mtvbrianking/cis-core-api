<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationPatientVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('station_patient_visit', function (Blueprint $table) {
            $table->uuid('visit_id');
            $table->uuid('station_id');
            $table->uuid('user_id');
            $table->text('instructions');
            $table->timestamps();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('concluded_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->unique(['visit_id', 'station_id']);

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
        Schema::table('station_patient_visit', function (Blueprint $table) {
            $table->dropForeign(['visit_id']);
            $table->dropForeign(['station_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('station_patient_visit');
    }
}
