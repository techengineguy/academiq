<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('hostel_building_id');
            $table->foreign('hostel_building_id')->references('id')->on('hostel_buildings')->onDelete('cascade');
            $table->string('room_number');
            $table->integer('floor');
            $table->integer('capacity');
            $table->integer('occupied')->default(0);
            $table->enum('room_type', ['single', 'double', 'triple', 'dormitory'])->default('double');
            $table->decimal('rent_amount', 10, 2)->nullable();
            $table->text('facilities')->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance', 'unavailable'])->default('available');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostel_rooms');
    }
};