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
            // Memastikan kolom paid_amount dan notes bisa NULL atau memiliki default value
            $table->bigInteger('paid_amount')->default(0)->nullable()->change();
            $table->text('notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_debts', function (Blueprint $table) {
            $table->bigInteger('paid_amount')->default(0)->nullable(false)->change();
            $table->text('notes')->nullable(false)->change();
        });
    }
};
