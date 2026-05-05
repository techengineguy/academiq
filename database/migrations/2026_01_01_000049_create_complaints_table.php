<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('institution_id')->index();
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->unsignedBigInteger('submitted_by')->index();
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('complaint_number')->unique();
            $table->string('subject');
            $table->text('description');
            $table->enum('category', ['academic', 'hostel', 'transport', 'infrastructure', 'staff', 'other'])->default('other')->index();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->index();
            $table->string('attachment')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open')->index();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
};