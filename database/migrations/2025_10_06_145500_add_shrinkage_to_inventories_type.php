<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'shrinkage' option to the enum column 'type'
        DB::statement("ALTER TABLE `inventories` MODIFY `type` ENUM('in','out','adjustment','shrinkage') NOT NULL DEFAULT 'in';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'shrinkage' option from 'type' enum
        DB::statement("ALTER TABLE `inventories` MODIFY `type` ENUM('in','out','adjustment') NOT NULL DEFAULT 'in';");
    }
};
