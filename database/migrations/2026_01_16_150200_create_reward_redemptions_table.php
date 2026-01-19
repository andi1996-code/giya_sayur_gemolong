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
        // Skip jika tabel sudah ada - akan dihandle oleh migration add_product_id
        if (Schema::hasTable('reward_redemptions')) {
            return;
        }

        // Tabel untuk tracking penukaran reward
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Link ke products table
            $table->integer('points_used'); // Poin yang digunakan
            $table->integer('quantity')->default(1); // Jumlah item ditukar
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null'); // Link ke transaksi (jika ditukar saat checkout)
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Kasir yang proses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};
