<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_sales', function (Blueprint $table) {
            $table->string('id', 11);
            $table->string('store_id', 11);
            $table->uuid('patient_id', 11)->nullable();
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->primary('id');

            $table->foreign('store_id')->references('id')->on('pharm_stores')
                ->onUpdate('restrict')->onDelete('restrict');

            // $table->foreign('patient_id')->references('id')->on('patients')
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
        Schema::table('pharm_sales', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            // $table->dropForeign(['patient_id']);
        });

        Schema::dropIfExists('pharm_sales');
    }
}