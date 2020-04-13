<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('patient_id');
            $table->uuid('user_id');
            // $table->enum('status', ['pending', 'started', 'concluded']);
            // $table->boolean('is_active')->default(false);
            // $table->timestamp('scheduled_for')->nullable();
            // $table->timestamp('started_at')->nullable();
            $table->timestamps();

            $table->primary('id');

            $table->foreign('patient_id')->references('id')->on('patients')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('user_id')->references('id')->on('users')
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
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('visits');
    }
}
