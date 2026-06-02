<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('paystack_subscription_code')->nullable()->after('cancellation_reason');
            $table->string('paystack_email_token')->nullable()->after('paystack_subscription_code');
            $table->string('paystack_customer_code')->nullable()->after('paystack_email_token');
            $table->string('paystack_reference')->nullable()->after('paystack_customer_code'); // last transaction ref
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'paystack_subscription_code',
                'paystack_email_token',
                'paystack_customer_code',
                'paystack_reference',
            ]);
        });
    }
};
