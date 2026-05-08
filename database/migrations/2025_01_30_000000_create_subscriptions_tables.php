<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('gateway_config_id')->nullable()->references('id')->on('gateway_configs')->onDelete('set null');
            $table->string('subscription_id')->nullable(); // the external subscription id
            $table->nullableMorphs('subscribable'); // subscribable_type, subscribable_id
            $table->string('status')->default('pending'); // pending, active, inactive
            $table->string('description');
            $table->string('currency')->default('USD');
            $table->decimal('amount', 20, 8)->default(0);
            $table->integer('frequency')->default(30); // The frequency of the subscription in days
            $table->string('cancel_reason')->nullable();
            $table->string('manage_url')->nullable();
            $table->string('success_url')->nullable();
            $table->string('cancel_url')->nullable();
            $table->string('handler')->nullable();
            $table->json('data')->nullable();
            $table->json('gateway_data')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_checked_at')->useCurrent();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('next_billing_at');
            $table->index(['status', 'next_billing_at']);
            $table->index('subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
