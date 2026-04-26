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
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['transfer', 'character', 'bonafide', 'completion', 'other'])->default('bonafide');
            $table->string('certificate_number')->unique();
            $table->date('issue_date');
            $table->text('purpose')->nullable();
            $table->text('content')->nullable();
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};