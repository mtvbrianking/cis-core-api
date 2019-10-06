<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->uuid('user_id')->nullable();
            $table->string('module_name');
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['module_name', 'name']);

            $table->foreign('module_name')->references('name')->on('modules')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
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
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('permissions');
    }
}
