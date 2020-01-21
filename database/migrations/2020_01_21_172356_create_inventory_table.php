<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('batch_id');
            $table->integer('quantity');

            $table->foreign('store_id')->references('id')->on('pharm_stores')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('batch_id')->references('id')->on('pharm_batches')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pharm_inventory', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['batch_id']);
        });

        Schema::dropIfExists('pharm_inventory');
    }
}
