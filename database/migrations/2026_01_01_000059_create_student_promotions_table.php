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
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('to_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('from_section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->foreignId('from_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('to_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->enum('status', ['promoted', 'detained', 'transferred'])->default('promoted');
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_promotions');
    }
};