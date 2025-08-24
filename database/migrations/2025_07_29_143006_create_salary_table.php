<?php

// database/migrations/[timestamp]_create_salaries_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
      Schema::create('salaries', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained()->onDelete('cascade');
        $table->string('employee_type')->nullable(); // removed `after(...)`
        $table->decimal('amount', 10, 2);
        $table->date('date');
        $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('salaries');
    }
};