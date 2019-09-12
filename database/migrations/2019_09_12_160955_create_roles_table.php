<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->index([
                'name',
            ], 'idx_role_name', 'btree');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
        });

        Schema::dropIfExists('roles');
    }
}
