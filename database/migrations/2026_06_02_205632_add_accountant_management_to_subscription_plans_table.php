<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('has_accountant_management')->default(false)->after('has_hostel_management');
        });

        // Enable for Professional and Enterprise, leave false for Starter
        DB::table('subscription_plans')
            ->whereIn('slug', ['professional', 'enterprise'])
            ->update(['has_accountant_management' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('has_accountant_management');
        });
    }
};
