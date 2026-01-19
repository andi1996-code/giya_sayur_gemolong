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
            // Drop existing foreign key constraint
            $table->dropForeign(['trigger_product_id']);

            // Modify column to be nullable
            $table->foreignId('trigger_product_id')->nullable()->change()->constrained('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            // Drop foreign key and restore
            $table->dropForeign(['trigger_product_id']);

            $table->foreignId('trigger_product_id')->change()->constrained('products')->onDelete('cascade');
        });
    }
};
