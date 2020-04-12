<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('station_user', function (Blueprint $table) {
            $table->uuid('station_id');
            $table->uuid('user_id');

            $table->primary(['station_id', 'user_id']);

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
        Schema::table('station_user', function (Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('station_user');
    }
}
