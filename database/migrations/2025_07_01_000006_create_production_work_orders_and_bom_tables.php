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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders'); // Optional link
            $table->foreignId('product_id')->constrained('products'); // Product to be manufactured
            $table->integer('quantity');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('status')->default('Planned')->comment('e.g., Planned, In Progress, Completed, Canceled');
            $table->foreignId('user_id')->constrained('users'); // User responsible for the work order
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products'); // Parent product
            $table->foreignId('component_product_id')->constrained('products'); // Component product
            $table->integer('quantity'); // Quantity of component needed
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
        Schema::dropIfExists('work_orders');
    }
};
