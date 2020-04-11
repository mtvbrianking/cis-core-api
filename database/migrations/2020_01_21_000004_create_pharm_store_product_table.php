<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmStoreProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_store_product', function (Blueprint $table) {
            $table->string('store_id', 11);
            $table->string('product_id', 11);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->unique(['store_id', 'product_id']);

            $table->foreign('store_id')->references('id')->on('pharm_stores')
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
        Schema::table('pharm_store_product', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::dropIfExists('pharm_store_product');
    }
}
