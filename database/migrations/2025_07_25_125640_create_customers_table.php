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
    // database/migrations/xxxx_create_customers_table.php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('id_number')->unique()->nullable();
    $table->string('phone')->unique();
    $table->string('email')->unique()->nullable();
    $table->string('country')->default('Rwanda');
    $table->date('dob')->nullable();
    $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
    $table->string('avatar')->nullable();
    $table->string('customer_type')->default('Retail'); // Retail, Wholesale, Contractor
    $table->string('province')->nullable();
    $table->string('district')->nullable();
    $table->string('sector')->nullable();
    $table->string('cell')->nullable();
    $table->string('village')->nullable();
    $table->text('address')->nullable();
     $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
