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
      Schema::create('production_stocks', function (Blueprint $table) {
        $table->id();
        $table->integer('total_quantity')->default(0);      // total bricks made
        $table->integer('remaining_quantity')->default(0);  // what is left
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_stocks');
    }
};
