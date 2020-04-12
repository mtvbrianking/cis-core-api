<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_sales', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('store_id');
            $table->uuid('user_id');
            $table->uuid('patient_id')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->primary('id');

            $table->foreign('store_id')->references('id')->on('pharm_stores')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('user_id')->references('id')->on('users')
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
            $table->dropForeign(['user_id']);
            // $table->dropForeign(['patient_id']);
        });

        Schema::dropIfExists('pharm_sales');
    }
}
