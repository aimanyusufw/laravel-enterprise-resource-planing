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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('transaction_type')->comment('e.g., GRN, Issue, Adjustment, Production');
            $table->integer('quantity_change'); // Positive for increase, negative for decrease
            $table->dateTime('transaction_date');
            $table->unsignedBigInteger('reference_document_id')->nullable(); // e.g., GRN ID, Sales Order Item ID
            $table->foreignId('user_id')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
