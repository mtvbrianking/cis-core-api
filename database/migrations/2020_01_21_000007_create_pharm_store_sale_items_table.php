<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmStoreSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_store_sale_items', function (Blueprint $table) {
            $table->string('id', 11);
            $table->string('sale_id', 11);
            $table->string('product_id', 11);
            $table->integer('quantity');
            $table->float('price');
            $table->timestamps();

            $table->primary('id');

            $table->foreign('sale_id')->references('id')->on('pharm_store_sales')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('product_id')->references('id')->on('pharm_products')
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
        Schema::table('pharm_store_sale_items', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::dropIfExists('pharm_store_sale_items');
    }
}
