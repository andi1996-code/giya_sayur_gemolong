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
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['buy_x_get_discount', 'bundle'])->default('buy_x_get_discount');
            $table->foreignId('trigger_product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('trigger_quantity', 10, 3)->default(1);
            $table->enum('trigger_unit', ['qty', 'kg'])->default('qty');
            $table->enum('discount_type', ['percentage', 'fixed', 'price'])->default('percentage');
            $table->integer('discount_value');
            $table->foreignId('apply_to_product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->integer('max_discount_per_transaction')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
