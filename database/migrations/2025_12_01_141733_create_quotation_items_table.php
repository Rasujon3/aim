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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');

            // Item information
            $table->string('name')->nullable();
            $table->text('details')->nullable();

            // Pricing
            $table->decimal('quantity', 15, 4)->default(1);
            $table->decimal('price', 15, 4)->default(0);
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('net_price', 15, 4)->default(0);

            // Tax & Discount
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->onDelete('cascade');
            $table->decimal('discount', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 4)->default(0);

            $table->decimal('tax_amount', 15, 4)->default(0);

            $table->decimal('total_discount_amount', 15, 4)->default(0);
            $table->decimal('total_tax_amount', 15, 4)->default(0);

            // Final line total
            $table->decimal('total', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
