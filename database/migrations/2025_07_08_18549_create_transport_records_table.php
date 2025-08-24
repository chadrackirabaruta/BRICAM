<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transport_records', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('transport_category_id')->constrained('transport_categories')->onDelete('cascade');
            $table->foreignId('stock_type_id')->nullable()->constrained('stock_types')->nullOnDelete();
            $table->foreignId('production_reference')->nullable()->constrained('productions')->nullOnDelete();

            // Dates
            $table->date('transport_date');
            $table->date('production_date')->nullable();

            // Quantity & pricing
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            // Locations
            $table->string('destination')->nullable();
            $table->string('source_location')->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');

            // Metadata
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();

            // Timestamps & soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transport_date');
            $table->index('production_date');
            $table->index(['employee_id', 'transport_date']);
            $table->index(['production_reference', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_records');
    }
};
