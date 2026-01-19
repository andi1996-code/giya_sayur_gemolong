<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom start_time dan end_time untuk filter berdasarkan jam
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('start_date')
                ->comment('Jam mulai filter laporan');
            $table->time('end_time')->nullable()->after('end_date')
                ->comment('Jam akhir filter laporan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
