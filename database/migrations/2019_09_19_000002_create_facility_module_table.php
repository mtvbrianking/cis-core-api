<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->uuid('facility_id');
            $table->string('module_name', 20);

            $table->primary(['facility_id', 'module_name']);

            $table->foreign('facility_id')->references('id')->on('facilities')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('module_name')->references('name')->on('modules')
                ->onUpdate('cascade')->onDelete('cascade');
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
