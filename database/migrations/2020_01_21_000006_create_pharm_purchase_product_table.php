<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmPurchaseProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_purchase_product', function (Blueprint $table) {
            $table->uuid('purchase_id');
            $table->uuid('product_id');
            $table->uuid('supplier_id')->nullable();
            $table->integer('quantity');
            $table->float('unit_price')->comment('Cost price');
            $table->string('mfr_batch_no', 255)->nullable();
            $table->date('mfd_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->unique(['purchase_id', 'product_id']);

            $table->foreign('purchase_id')->references('id')->on('pharm_purchases')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('product_id')->references('id')->on('pharm_products')
                ->onUpdate('restrict')->onDelete('restrict');

            // $table->foreign('supplier_id')->references('id')->on('pharm_suppliers')
            //     ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pharm_purchase_product', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropForeign(['product_id']);
            // $table->dropForeign(['supplier_id']);
        });

        Schema::dropIfExists('pharm_purchase_product');
    }
}
