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
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Paystack plan codes — one per billing cycle
            $table->string('paystack_monthly_plan_code')->nullable()->after('is_active');
            $table->string('paystack_yearly_plan_code')->nullable()->after('paystack_monthly_plan_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['paystack_monthly_plan_code', 'paystack_yearly_plan_code']);
        });
    }
};
