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
        Schema::create('stock_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Example: Mabisi, Gutwikwa, Ahiye
            $table->foreignId('parent_id')->nullable()->constrained('stock_types')->nullOnDelete();
            $table->integer('flow_stage')->nullable();
            $table->boolean('decrease_from')->default(0);
            $table->boolean('increase_to')->default(0);
            $table->integer('decrease_amount')->nullable(); // no `after`
            $table->integer('increase_amount')->nullable(); // no `after`
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_types');
    }
};
