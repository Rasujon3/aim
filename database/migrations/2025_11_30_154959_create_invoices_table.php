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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number')->nullable()->unique();

            // Relations
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');

            // Core invoice fields
            $table->date('date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('reference')->nullable();
            $table->string('hash')->nullable();

            // Amounts
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);

            // Order-level taxes & discounts
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->onDelete('cascade');
            $table->decimal('order_tax_amount', 15, 2)->default(0);
            $table->decimal('product_tax_amount', 15, 2)->default(0);
            $table->decimal('total_tax_amount', 15, 2)->default(0);

            $table->decimal('order_discount', 15, 2)->default(0);
            $table->decimal('order_discount_amount', 15, 2)->default(0);
            $table->decimal('product_discount_amount', 15, 2)->default(0);
            $table->decimal('total_discount_amount', 15, 2)->default(0);

            // Payment
            $table->decimal('paid', 15, 2)->default(0);

            // Note
            $table->text('note')->nullable();

            $table->string('status')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
