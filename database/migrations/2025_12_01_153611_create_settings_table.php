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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Logos
            $table->string('login_logo')->nullable();
            $table->string('invoice_logo')->nullable();

            // General Settings
            $table->string('site_name')->nullable();
            $table->string('reference')->nullable();
            $table->string('reference_prefix')->nullable();
            $table->string('currency_code')->nullable();

            // Number Formatting
            $table->string('invoice_number_prefix')->nullable();
            $table->string('quotation_number_prefix')->nullable();
            $table->string('payment_number_prefix')->nullable();

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
        Schema::dropIfExists('settings');
    }
};
