<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('facility_id');
            $table->string('first_name')->comment('Given name');
            $table->string('last_name')->comment('Surname name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('nin', 14)->nullable()->comment('National ID Number');

            $table->float('weight')->nullable()->comment('Kilograms');
            $table->float('height')->nullable()->comment('Centimeters');
            $table->enum('blood_type', ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-', 'AB-'])->nullable();
            $table->string('existing_conditions', 255)->nullable()->comment('Like; Hypertension, Diabetes, Pregancy,...');
            $table->string('allergies', 255)->nullable();
            $table->text('notes')->nullable()->comment('Patient disclosed notes.');
            $table->string('next_of_kin', 255)->nullable()->comment('No. Name Relation Contact');
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
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        Schema::dropIfExists('patients');
    }
}
