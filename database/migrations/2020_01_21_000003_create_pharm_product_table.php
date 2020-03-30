<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pharm_products', function (Blueprint $table) {
            $table->string('id', 11);
            $table->uuid('facility_id');
            $table->string('name', 255);
            $table->string('brand', 255);
            $table->string('manufacturer', 255)->nullable();
            $table->string('category', 150)->nullable(); //enum
            $table->string('concentration', 100)->nullable();
            $table->enum('package', ['tablet', 'pce', 'bottle']);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

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
        Schema::table('pharm_products', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        Schema::dropIfExists('pharm_products');
    }
}
