<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('point_per_amount', 15, 2)->default(10000); // Setiap Rp 10.000 = 1 poin
            $table->integer('points_earned')->default(1); // Poin yang didapat
            $table->decimal('point_value', 15, 2)->default(1000); // Nilai 1 poin = Rp 1.000
            $table->integer('min_points_redeem')->default(10); // Minimal poin untuk ditukar
            $table->integer('point_expiry_days')->default(365); // Poin kadaluarsa dalam 365 hari
            $table->boolean('auto_tier_upgrade')->default(true); // Otomatis upgrade tier

            // Tier requirements based on total spent
            $table->decimal('bronze_min_spent', 15, 2)->default(0);
            $table->decimal('silver_min_spent', 15, 2)->default(1000000); // Rp 1 juta
            $table->decimal('gold_min_spent', 15, 2)->default(5000000); // Rp 5 juta
            $table->decimal('platinum_min_spent', 15, 2)->default(10000000); // Rp 10 juta

            // Bonus point multiplier per tier
            $table->decimal('bronze_multiplier', 5, 2)->default(1.0); // 1x
            $table->decimal('silver_multiplier', 5, 2)->default(1.2); // 1.2x
            $table->decimal('gold_multiplier', 5, 2)->default(1.5); // 1.5x
            $table->decimal('platinum_multiplier', 5, 2)->default(2.0); // 2x

            $table->timestamps();
        });

        // Insert default settings
        DB::table('point_settings')->insert([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_settings');
    }
};
