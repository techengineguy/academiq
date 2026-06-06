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
        Schema::table('academic_years', function (Blueprint $table) {
            // Add a partial unique index to ensure only one current academic year per institution
            // Note: MySQL doesn't support partial indexes directly, so we handle this at the application level
            // This is a comment documenting the business rule
            $table->index(['institution_id', 'is_current'], 'idx_institution_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropIndex('idx_institution_current');
        });
    }
};
