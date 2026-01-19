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
            // Modify stock columns to support decimal values (for accurate weight-based stock management)
            $table->decimal('stock', 10, 3)->nullable()->change();
            $table->decimal('stok_kongsi', 10, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to integer
            $table->integer('stock')->nullable()->change();
            $table->integer('stok_kongsi')->nullable()->change();
        });
    }
};
