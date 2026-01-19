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
        Schema::table('settings', function (Blueprint $table) {
            $table->text('receipt_footer_line1')->nullable()->after('auto_print');
            $table->text('receipt_footer_line2')->nullable()->after('receipt_footer_line1');
            $table->text('receipt_footer_line3')->nullable()->after('receipt_footer_line2');
            $table->text('receipt_footer_note')->nullable()->after('receipt_footer_line3');
            $table->boolean('show_footer_thank_you')->default(true)->after('receipt_footer_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_footer_line1',
                'receipt_footer_line2',
                'receipt_footer_line3',
                'receipt_footer_note',
                'show_footer_thank_you'
            ]);
        });
    }
};
