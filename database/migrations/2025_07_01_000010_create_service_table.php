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
        Schema::create('service_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('product_id')->nullable()->constrained('products'); // Optional link
            $table->text('issue_description');
            $table->dateTime('ticket_date');
            $table->string('status')->default('Open')->comment('e.g., Open, In Progress, Closed, Escalated');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users');
            $table->text('resolution_details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tickets');
    }
};
