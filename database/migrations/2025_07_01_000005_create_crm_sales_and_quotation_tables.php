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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->dateTime('order_date');
            $table->string('status')->default('Pending')->comment('e.g., Pending, Approved, Shipped, Completed');
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->foreignId('user_id')->constrained('users'); // Sales person who created the order
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products'); // Will be created in production_tables migration
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->dateTime('quotation_date');
            $table->dateTime('valid_until')->nullable();
            $table->string('status')->default('Draft')->comment('e.g., Draft, Submitted, Approved, Rejected');
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->foreignId('user_id')->constrained('users'); // User who created the quotation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
