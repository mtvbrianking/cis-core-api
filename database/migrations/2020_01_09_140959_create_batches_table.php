<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('facility_id');
            $table->unsignedBigInteger('catalog_id');
            $table->float('cost_price');           

            $table->date('manufactured_date');
            $table->date('expires_at');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('facility_id')->references('id')->on('facilities')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('catalog_id')->references('id')->on('pharm_catalog')
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
        Schema::table('pharm_batches', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropForeign(['catalog_id']);
        });

        Schema::dropIfExists('pharm_batches');
    }
}
