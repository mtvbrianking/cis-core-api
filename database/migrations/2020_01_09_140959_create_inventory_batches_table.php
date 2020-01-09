<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('inventory_id');
            $table->uuid('facility_id');

            $table->string('batch_no', 50);
            $table->date('manufacturing_date');
            $table->date('expiry_date');

            $table->integer('packages');
            $table->integer('units_per_package');

            $table->integer('package_cost_price');
            $table->integer('package_selling_price');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('facility_id')->references('id')->on('facilities')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('inventory_id')->references('id')->on('inventory')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_batches');
    }
}
