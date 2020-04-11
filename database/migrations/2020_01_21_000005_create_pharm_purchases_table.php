<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_purchases', function (Blueprint $table) {
            $table->string('id', 11);
            $table->string('store_id', 11);
<<<<<<< HEAD:database/migrations/2020_01_21_000005_create_pharm_purchases_table.php
            $table->uuid('user_id');
            $table->decimal('total', 10, 2);
=======
            $table->string('product_id', 11);
            $table->integer('quantity');
            $table->float('unit_price')->comment('Cost price');
            $table->string('mfr_batch_no', 255)->nullable();
            $table->date('mfd_at')->nullable();
            $table->date('expires_at')->nullable();
>>>>>>> patients:database/migrations/2020_01_21_000004_create_pharm_batches_table.php
            $table->timestamps();

            $table->primary('id');

            $table->foreign('store_id')->references('id')->on('pharm_stores')
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
        Schema::table('pharm_purchases', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('pharm_purchases');
    }
}
