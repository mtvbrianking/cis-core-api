<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_stores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('facility_id');
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')
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
        Schema::table('pharm_stores', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        Schema::dropIfExists('pharm_stores');
    }
}
