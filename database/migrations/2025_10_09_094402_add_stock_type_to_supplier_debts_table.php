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
        Schema::table('supplier_debts', function (Blueprint $table) {
            $table->enum('stock_type', ['regular', 'kongsi'])->default('regular')->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_debts', function (Blueprint $table) {
            $table->dropColumn('stock_type');
        });
    }
};
