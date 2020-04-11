<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_meta', function (Blueprint $table) {
            $table->string('id', 11);
            $table->uuid('facility_id');
            $table->string('name', 50);
            $table->string('description', 255);
            $table->boolean('confidential', false);
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->foreign('facility_id')->references('id')->on('facilities')
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
        Schema::table('patient_meta', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        Schema::dropIfExists('patient_meta');
    }
}
