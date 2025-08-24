
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionsTable extends Migration
{
    public function up()
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('production_date');
            $table->integer('quantity')->unsigned();
            $table->string('product_type');
            $table->decimal('unit_price', 10, 2);
            $table->string('reference_number')->unique();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('completed');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productions');
    }
}
