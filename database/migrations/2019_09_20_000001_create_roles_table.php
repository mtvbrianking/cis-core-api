<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->uuid('facility_id');
            $table->string('name', 50);
            $table->string('description', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->index('name', 'idx_role_name', 'btree');

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
        DB::raw('DROP INDEX IF EXISTS idx_role_name');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        Schema::dropIfExists('roles');
    }
}
