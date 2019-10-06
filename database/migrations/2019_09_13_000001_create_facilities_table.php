<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->nullable();
            $table->string('name', 100);
            $table->string('description', 100)->nullable();
            $table->string('address', 100);
            $table->string('email', 50);
            $table->string('website', 50)->nullable();
            $table->string('phone', 25)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

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
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('facilities');
    }
}
