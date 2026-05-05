<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('institution_id')->index();
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->unsignedBigInteger('academic_year_id')->index();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->unsignedBigInteger('class_id')->index();
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->string('application_number')->unique();
            $table->date('application_date')->index();
            $table->string('student_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('parent_phone');
            $table->string('parent_email')->nullable();
            $table->text('address');
            $table->string('previous_school')->nullable();
            $table->string('birth_certificate')->nullable();
            $table->string('previous_marksheet')->nullable();
            $table->string('transfer_certificate')->nullable();
            $table->string('student_photo')->nullable();
            $table->date('test_date')->nullable();
            $table->decimal('test_marks', 5, 2)->nullable();
            $table->date('interview_date')->nullable();
            $table->text('interview_remarks')->nullable();
            $table->enum('status', ['submitted', 'under_review', 'test_scheduled', 'interview_scheduled', 'approved', 'rejected', 'admitted'])->default('submitted')->index();
            $table->unsignedBigInteger('reviewed_by')->nullable()->index();
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admission_applications');
    }
};