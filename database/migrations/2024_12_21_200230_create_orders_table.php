<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('package_id')->constrained();
            $table->foreignId('package_price_id')->nullable()->constrained()->onDelete('set null');
            $table->string('external_id')->nullable();
            $table->string('status')->default('active');
            $table->decimal('cycle_price', 20, 8)->default(0);
            $table->decimal('setup_fee', 20, 8)->default(0);
            $table->decimal('upgrade_fee', 20, 8)->default(0);
            $table->integer('period_in_days')->default(0);
            $table->timestamp('due_date')->nullable();
            $table->timestamp('last_renewed_at')->useCurrent();
            $table->boolean('auto_balance_renew')->default(false);
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index(['status', 'due_date']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('order_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('cycle_price', 20, 8)->default(0);
            $table->decimal('upgrade_fee', 20, 8)->default(0);
            $table->string('description');
            $table->string('type')->nullable();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_prices');
    }
};
