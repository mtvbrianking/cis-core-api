<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmSaleProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_sale_product', function (Blueprint $table) {
            $table->uuid('sale_id');
            $table->uuid('product_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);

            $table->unique(['sale_id', 'product_id']);

            $table->foreign('sale_id')->references('id')->on('pharm_sales')
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
        Schema::table('pharm_sale_product', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::dropIfExists('pharm_sale_product');
    }
}
