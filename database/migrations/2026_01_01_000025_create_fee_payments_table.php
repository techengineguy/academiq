<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('uuid')->on('institutions')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('fee_invoice_id')->index();
            $table->foreign('fee_invoice_id')->references('id')->on('fee_invoices')->onDelete('cascade');
            $table->unsignedBigInteger('student_id')->index();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date')->index();
            $table->enum('payment_method', ['cash', 'cheque', 'card', 'online', 'bank_transfer'])->default('cash')->index();
            $table->string('transaction_id')->nullable()->index();
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('received_by')->nullable()->index();
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_payments');
    }
};