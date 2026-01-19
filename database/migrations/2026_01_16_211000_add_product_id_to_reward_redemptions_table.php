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
        Schema::table('reward_redemptions', function (Blueprint $table) {
            // Add product_id if not exists
            if (!Schema::hasColumn('reward_redemptions', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('member_id')->constrained()->onDelete('cascade');
            }

            // Add processed_by if not exists
            if (!Schema::hasColumn('reward_redemptions', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            if (Schema::hasColumn('reward_redemptions', 'processed_by')) {
                $table->dropForeign(['processed_by']);
                $table->dropColumn('processed_by');
            }

            if (Schema::hasColumn('reward_redemptions', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
        });
    }
};
