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
        Schema::table('promos', function (Blueprint $table) {
            $table->integer('minimum_purchase')->nullable()->after('trigger_unit')->comment('Minimum total belanja untuk trigger promo');
            $table->foreignId('free_product_id')->nullable()->after('apply_to_product_id')->constrained('products')->onDelete('cascade')->comment('Produk yang dapat diskon/gratis');
            $table->decimal('free_quantity', 10, 3)->nullable()->after('free_product_id')->comment('Kuantitas produk gratis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn(['minimum_purchase', 'free_product_id', 'free_quantity']);
        });
    }
};
