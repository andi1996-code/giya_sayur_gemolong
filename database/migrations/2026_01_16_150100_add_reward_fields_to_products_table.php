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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_reward')->default(false)->after('is_active'); // Apakah produk ini untuk redeem poin
            $table->integer('points_required')->nullable()->after('is_reward'); // Poin yang dibutuhkan untuk tukar (jika is_reward = true)
            $table->integer('max_redeem_per_member')->nullable()->after('points_required'); // Maks berapa kali member bisa tukar (null = unlimited)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_reward', 'points_required', 'max_redeem_per_member']);
        });
    }
};
