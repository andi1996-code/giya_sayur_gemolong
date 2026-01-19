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
        Schema::create('supplier_debts', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('supplier_phone')->nullable();
            $table->text('supplier_address')->nullable();
            $table->enum('transaction_type', ['hutang', 'piutang']); // hutang = kita berhutang ke supplier, piutang = supplier berhutang ke kita
            $table->bigInteger('amount'); // jumlah dalam rupiah
            $table->text('description');
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['belum_lunas', 'sebagian_lunas', 'lunas'])->default('belum_lunas');
            $table->bigInteger('paid_amount')->default(0);
            $table->bigInteger('remaining_amount')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // user yang menginput
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_debts');
    }
};
