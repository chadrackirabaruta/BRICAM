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
        Schema::create('brick_stocks', function (Blueprint $table) {
            $table->id();

            // 🔗 Link to stock type
            $table->foreignId('stock_type_id')
                  ->constrained('stock_types')
                  ->onDelete('cascade');

            // Current stock quantity (cannot be negative)
            $table->unsignedInteger('quantity')->default(0);

            $table->timestamps();

            // Index for faster lookup by stock type
            $table->unique('stock_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brick_stocks');
    }
};
