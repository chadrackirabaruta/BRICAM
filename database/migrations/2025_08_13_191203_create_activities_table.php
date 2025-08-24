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
   
Schema::create('activities', function (Blueprint $table) {
    $table->id();
    $table->string('type'); // e.g., 'sale', 'purchase', 'user'
    $table->string('title');
    $table->text('description');
    $table->foreignId('user_id')->nullable()->constrained();
    $table->morphs('subject'); // Polymorphic relation
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
