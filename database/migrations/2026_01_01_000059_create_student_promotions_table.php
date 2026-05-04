<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unsignedBigInteger('from_class_id');
            $table->foreign('from_class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->unsignedBigInteger('to_class_id');
            $table->foreign('to_class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->unsignedBigInteger('from_section_id')->nullable();
            $table->foreign('from_section_id')->references('id')->on('sections')->onDelete('set null');
            $table->unsignedBigInteger('to_section_id')->nullable();
            $table->foreign('to_section_id')->references('id')->on('sections')->onDelete('set null');
            $table->unsignedBigInteger('from_academic_year_id');
            $table->foreign('from_academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->unsignedBigInteger('to_academic_year_id');
            $table->foreign('to_academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->enum('status', ['promoted', 'detained', 'transferred'])->default('promoted');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_promotions');
    }
};