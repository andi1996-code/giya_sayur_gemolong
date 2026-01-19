<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan pengaturan printer per-user untuk mendukung multi-kasir
     * Setiap kasir bisa mengatur printer masing-masing sesuai PC yang digunakan
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('printer_name')->nullable()->after('password')
                ->comment('Nama printer yang digunakan kasir ini');
            $table->boolean('print_via_bluetooth')->default(false)->after('printer_name')
                ->comment('Tipe koneksi: false=Kabel, true=Bluetooth');
            $table->boolean('auto_print')->default(false)->after('print_via_bluetooth')
                ->comment('Cetak otomatis setelah transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['printer_name', 'print_via_bluetooth', 'auto_print']);
        });
    }
};
