<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('id_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('card_number')->unique();
            $table->enum('type', ['student', 'teacher', 'staff'])->default('student');
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('barcode')->nullable();
            $table->enum('status', ['active', 'expired', 'lost', 'damaged'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('id_cards');
    }
};