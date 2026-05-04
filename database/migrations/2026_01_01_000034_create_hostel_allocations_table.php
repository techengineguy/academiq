<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('student_id')->index();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unsignedBigInteger('hostel_room_id')->index();
            $table->foreign('hostel_room_id')->references('id')->on('hostel_rooms')->onDelete('cascade');
            $table->unsignedBigInteger('academic_year_id')->index();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->date('allocated_date')->index();
            $table->date('vacated_date')->nullable()->index();
            $table->integer('bed_number')->nullable();
            $table->enum('status', ['active', 'vacated', 'transferred'])->default('active')->index();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostel_allocations');
    }
};