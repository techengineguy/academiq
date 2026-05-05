<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->enum('type', ['transfer', 'character', 'bonafide', 'completion', 'other'])->default('bonafide');
            $table->string('certificate_number')->unique();
            $table->date('issue_date');
            $table->text('purpose')->nullable();
            $table->text('content')->nullable();
            $table->unsignedBigInteger('issued_by');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};