<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('invoice_id')->nullable(); // invoice id for the payment i,e INV-2025-0001
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('gateway_config_id')->nullable()->references('id')->on('gateway_configs')->onDelete('set null');
            $table->nullableMorphs('payable'); // payable_type, payable_id
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('unpaid');
            $table->string('description');

            $table->string('currency')->default('USD');
            $table->decimal('subtotal', 20, 8)->default(0);
            $table->decimal('discount', 20, 8)->default(0);
            $table->decimal('tax', 20, 8)->default(0);
            $table->decimal('total', 20, 8)->default(0); // Total amount after tax and discount
            $table->decimal('earnings', 20, 8)->default(0); // Earnings after fees

            $table->string('success_url')->nullable();
            $table->string('cancel_url')->nullable();
            $table->string('handler')->nullable();
            $table->json('data')->nullable();
            $table->json('gateway_data')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('paid_at');
            $table->index(['status', 'paid_at']);
            $table->index('transaction_id');
        });

        Schema::create('payment_tax_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('tax_name');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('tax_exempt')->default(false);
            $table->string('tax_exempt_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_tax_details');
    }
};
