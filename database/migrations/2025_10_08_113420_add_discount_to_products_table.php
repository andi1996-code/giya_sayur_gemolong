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
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('price_per_kg');
            $table->boolean('discount_active')->default(false)->after('discount_percentage');
            $table->timestamp('discount_start_date')->nullable()->after('discount_active');
            $table->timestamp('discount_end_date')->nullable()->after('discount_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['discount_percentage', 'discount_active', 'discount_start_date', 'discount_end_date']);
        });
    }
};
