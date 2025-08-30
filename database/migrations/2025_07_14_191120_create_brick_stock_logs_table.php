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
        Schema::create('brick_stock_logs', function (Blueprint $table) {
            $table->id();

            // 🔗 Employee (must be a user in 'users' table)
            $table->foreignId('employee_id')
                  ->constrained('users')   // FK to users table
                  ->onDelete('cascade');

            // 🔗 Stock type
            $table->foreignId('stock_type_id')
                  ->constrained('stock_types')
                  ->onDelete('cascade');

            // ✅ Actions
            $table->enum('action', [
                'increase',       // Stock added manually or via transport
                'decrease',       // Stock removed/sold/transported
                'correction',     // Reverse or manual stock fix (e.g., sale deleted)
                'reverse_sale',   // Sale reversal specifically
                'adjustment',     // Admin manual adjustment
            ])->default('increase')->index();

            $table->unsignedInteger('quantity');

            $table->date('stock_date')->index();

            // Reference to related record (sale, transport, adjustment, etc.)
            $table->unsignedBigInteger('reference')->nullable()->index();

            // Explanation for this log entry
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Optional: add composite index for faster queries by stock_type + date
            $table->index(['stock_type_id', 'stock_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brick_stock_logs');
    }
};
