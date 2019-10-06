<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilityModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_module', function (Blueprint $table) {
            $table->integer('facility_id')->unsigned();
            $table->string('module_name', 20);

            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreign('module_name')->references('name')->on('modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facility_module', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropForeign(['module_name']);
        });

        Schema::dropIfExists('facility_module');
    }
}
