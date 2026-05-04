<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('institution_id')->index();
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->unsignedBigInteger('created_by')->index();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('attachment')->nullable();
            $table->enum('target_audience', ['all', 'students', 'teachers', 'parents', 'staff', 'specific_class'])->default('all');
            $table->json('target_classes')->nullable();
            $table->date('publish_date')->index();
            $table->date('expiry_date')->nullable()->index();
            $table->boolean('is_urgent')->default(false)->index();
            $table->boolean('send_notification')->default(false);
            $table->enum('status', ['draft', 'published', 'expired'])->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};