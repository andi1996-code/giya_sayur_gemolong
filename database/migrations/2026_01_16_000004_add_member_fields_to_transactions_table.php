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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('payment_method_id')->constrained()->onDelete('set null');
            $table->integer('points_earned')->default(0)->after('promo_discount'); // Poin yang didapat
            $table->integer('points_redeemed')->default(0)->after('points_earned'); // Poin yang digunakan
            $table->decimal('points_discount', 15, 2)->default(0)->after('points_redeemed'); // Diskon dari poin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn(['member_id', 'points_earned', 'points_redeemed', 'points_discount']);
        });
    }
};
