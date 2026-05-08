<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('connection_id')->constrained('server_connections');
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon')->default('default.png');
            $table->string('status');
            $table->integer('global_quantity')->default(-1);
            $table->integer('client_quantity')->default(-1);
            $table->json('data')->nullable();
            $table->boolean('allow_notes')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('short_description')->nullable();
            $table->integer('period_in_days')->default(0);
            $table->decimal('price', 20, 8)->default(0);
            $table->decimal('setup_fee', 20, 8)->default(0);
            $table->decimal('upgrade_fee', 20, 8)->default(0);
            $table->json('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('package_config_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('key');
            $table->string('type');
            $table->string('rules')->default('required');
            $table->string('default_value');
            $table->integer('onetime_day_equivalent')->default(365);
            $table->json('data')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['package_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
        Schema::dropIfExists('package_features');
        Schema::dropIfExists('package_prices');
        Schema::dropIfExists('package_config_options');
    }
};
