<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_user_store', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->unsignedBigInteger('store_id');

            $table->primary(['user_id', 'store_id']);

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('store_id')->references('id')->on('pharm_stores')
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
        Schema::table('pharm_user_store', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['store_id']);
        });

        Schema::dropIfExists('pharm_user_store');
    }
}
