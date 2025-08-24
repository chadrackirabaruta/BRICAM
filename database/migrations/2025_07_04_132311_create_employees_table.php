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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('id_number', 16)->nullable();
            $table->date('dob');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('avatar')->nullable(); // File path for avatar

            // Relationships
            $table->unsignedBigInteger('employee_type_id');
            $table->unsignedBigInteger('salary_type_id');

            // Location information (stored as region names)
            $table->string('country')->default('Rwanda');
            $table->string('province');
            $table->string('district');
            $table->string('sector');
            $table->string('cell');
            $table->string('village');

            // Status & timestamps
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_type_id')->references('id')->on('employee_types')->onDelete('cascade');
            $table->foreign('salary_type_id')->references('id')->on('salary_types')->onDelete('cascade');

            // Indexes
            $table->index('active');
            $table->index('employee_type_id');
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};