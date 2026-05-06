<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('institution_id')->index();
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->date('joining_date');
            $table->string('designation');
            $table->string('department')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('employment_type', ['permanent', 'temporary', 'contract'])->default('permanent');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff');
    }
};