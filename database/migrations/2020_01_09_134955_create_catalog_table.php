<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_catalog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('facility_id');
            $table->string('name', 255);
            $table->string('brand', 255);
            $table->string('concentration', 100)->nullable();
            $table->enum('package', ['tablet', 'syrup', 'pcs', 'bottles']);
            $table->text('description')->nullable();
            $table->float('sell_at');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('facility_id')->references('id')->on('facilities')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pharm_catalog', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        Schema::dropIfExists('pharm_catalog');
    }
}
