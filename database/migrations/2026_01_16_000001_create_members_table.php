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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique(); // Kode member unik (contoh: MBR001)
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->unique();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->integer('total_points')->default(0); // Total poin saat ini
            $table->integer('lifetime_points')->default(0); // Total poin sepanjang masa
            $table->decimal('total_spent', 15, 2)->default(0); // Total belanja sepanjang masa
            $table->integer('total_transactions')->default(0); // Jumlah transaksi
            $table->date('registered_date');
            $table->date('last_transaction_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
