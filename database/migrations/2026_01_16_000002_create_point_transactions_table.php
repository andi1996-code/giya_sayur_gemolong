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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null'); // Transaksi POS terkait
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted']); // Jenis transaksi poin
            $table->integer('points'); // Jumlah poin (positif untuk earned, negatif untuk redeemed)
            $table->integer('balance_before'); // Saldo sebelum transaksi
            $table->integer('balance_after'); // Saldo setelah transaksi
            $table->decimal('transaction_amount', 15, 2)->nullable(); // Nilai transaksi (untuk earned)
            $table->text('description')->nullable(); // Deskripsi transaksi
            $table->date('expired_at')->nullable(); // Tanggal kadaluarsa poin (untuk earned)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
