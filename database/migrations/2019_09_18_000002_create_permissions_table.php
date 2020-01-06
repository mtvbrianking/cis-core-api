<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('module_name', 25);
            $table->string('name', 25);
            $table->string('description', 100)->nullable();
            $table->timestamps();

            $table->unique(['module_name', 'name']);

            $table->foreign('module_name')->references('name')->on('modules')
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
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['module_name']);
        });

        Schema::dropIfExists('permissions');
    }
}
