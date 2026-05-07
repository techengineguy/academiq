<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->unsignedBigInteger('institution_id')->index();
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->unsignedBigInteger('class_id')->nullable()->index();
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
            $table->unsignedBigInteger('section_id')->nullable()->index();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            $table->unsignedBigInteger('academic_year_id')->index();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->string('admission_number')->unique();
            $table->date('admission_date')->index();
            $table->string('roll_number')->nullable()->index();
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('category')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('birth_certificate')->nullable();
            $table->string('transfer_certificate')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->string('house')->nullable();
            $table->enum('status', ['active', 'inactive', 'transferred', 'graduated'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};