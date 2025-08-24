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
      // In the migration file created with the model
Schema::create('sale_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 10, 2);
    $table->string('payment_method')->default('cash');
    $table->date('payment_date');
    $table->string('reference')->nullable();
    $table->text('notes')->nullable();
    $table->foreignId('recorded_by')->constrained('users');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
