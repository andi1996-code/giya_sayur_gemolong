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
        // Cek apakah tabel reward_items sudah ada
        if (!Schema::hasTable('reward_items')) {
            Schema::create('reward_items', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Nama hadiah
                $table->text('description')->nullable(); // Deskripsi hadiah
                $table->string('image')->nullable(); // Foto hadiah
                $table->integer('points_required'); // Poin yang dibutuhkan untuk tukar
                $table->integer('stock')->default(0); // Stok hadiah tersedia
                $table->boolean('is_active')->default(true); // Aktif/nonaktif
                $table->integer('max_redeem_per_member')->nullable(); // Maks. berapa kali member bisa tukar (null = unlimited)
                $table->timestamps();
            });
        }

        // Tabel untuk tracking penukaran reward
        if (!Schema::hasTable('reward_redemptions')) {
            Schema::create('reward_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('member_id')->constrained()->onDelete('cascade');
                $table->foreignId('reward_item_id')->constrained()->onDelete('cascade');
                $table->integer('points_used'); // Poin yang digunakan
                $table->integer('quantity')->default(1); // Jumlah item ditukar
                $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null'); // Link ke transaksi (jika ditukar saat checkout)
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('reward_items');
    }
};
