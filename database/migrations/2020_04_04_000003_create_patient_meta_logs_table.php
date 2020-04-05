<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientMetaLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_meta_logs', function (Blueprint $table) {
            $table->string('id', 11);
            $table->string('patient_id', 11);
            $table->string('patient_meta_id', 11);
            $table->string('value', 255);

            $table->timestamps();

            $table->primary('id');

            $table->foreign('patient_id')->references('id')->on('patients')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('patient_meta_id')->references('id')->on('patient_meta')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_meta_logs', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['patient_meta_id']);
        });

        Schema::dropIfExists('patient_meta_logs');
    }
}
