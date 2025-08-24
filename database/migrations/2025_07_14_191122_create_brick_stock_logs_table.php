<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('brick_stock_logs', function (Blueprint $table) {
            $table->id();

            // 🔗 Employee who made the movement
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            // 🔗 Type of stock (Amatafari mabisi, Gutwikwa, Ahiye…)
            $table->foreignId('stock_type_id')->constrained('stock_types')->onDelete('cascade');

            // ✅ Expanded actions for full tracking
            $table->enum('action', [
                'increase',         // Stock added manually or via transport
                'decrease',         // Stock removed/sold/transported
                'correction',       // Reverse or manual stock fix (e.g., sale deleted)
                'reverse_sale',     // Sale reversal specifically
                'adjustment',       // Admin manual adjustment
            ])->default('increase')->index();

            $table->unsignedInteger('quantity');

            $table->date('stock_date')->index();

            // Optional reference to another system (e.g. sale_id, transport_id, adjustment_id)
            $table->string('reference')->nullable();

            // Explanation or context for the action
            $table->text('remarks')->nullable();

            $table->timestamps();
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