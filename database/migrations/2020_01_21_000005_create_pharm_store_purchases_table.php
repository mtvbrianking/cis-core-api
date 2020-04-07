<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmStorePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_store_purchases', function (Blueprint $table) {
            $table->string('id', 11);
            $table->string('store_id', 11);
            $table->string('product_id', 11);
            $table->integer('quantity');
            $table->float('unit_price')->comment('Cost price');
            $table->string('mfr_batch_no', 255)->nullable();
            $table->date('mfd_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();

            $table->primary('id');

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
        Schema::table('pharm_store_purchases', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::dropIfExists('pharm_store_purchases');
    }
}