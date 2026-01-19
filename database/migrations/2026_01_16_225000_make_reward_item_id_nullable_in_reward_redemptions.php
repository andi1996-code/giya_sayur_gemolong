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
            // Make reward_item_id nullable since we're now using product_id
            if (Schema::hasColumn('reward_redemptions', 'reward_item_id')) {
                $table->unsignedBigInteger('reward_item_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            if (Schema::hasColumn('reward_redemptions', 'reward_item_id')) {
                $table->unsignedBigInteger('reward_item_id')->nullable(false)->change();
            }
        });
    }
};
