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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->dateTime('request_date');
            $table->foreignId('requested_by_user_id')->constrained('users');
            $table->foreignId('product_id')->constrained('products'); // Will be created in production_tables migration
            $table->integer('quantity');
            $table->string('status')->default('Pending')->comment('e.g., Pending, Approved, Rejected, Ordered');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->dateTime('order_date');
            $table->dateTime('delivery_date')->nullable();
            $table->string('status')->default('Draft')->comment('e.g., Draft, Pending Approval, Approved, Received, Closed');
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->foreignId('user_id')->constrained('users'); // User who created the PO
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products'); // Will be created in production_tables migration
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('goods_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->dateTime('receipt_date');
            $table->foreignId('received_by_user_id')->constrained('users');
            $table->string('status')->comment('e.g., Partial, Complete');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_notes');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('suppliers');
    }
};
