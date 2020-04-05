<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_visits', function (Blueprint $table) {
            $table->string('id', 11);
            $table->string('patient_id', 11);
            $table->string('station_id', 11);
            $table->boolean('is_active')->default(true)->comment('Active when the patient is at premises');
            $table->timestamp('created_at');
            $table->timestamp('scheduled_for');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('concluded_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->primary('id');

            $table->foreign('patient_id')->references('id')->on('patients')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('station_id')->references('id')->on('stations')
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
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('patient_visits');
    }
}
