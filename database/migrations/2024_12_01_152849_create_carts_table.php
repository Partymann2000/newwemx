<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index('session_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->nullableMorphs('cartable'); // Allows polymorphic relations for different item types
            $table->string('name')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('price', 20, 8)->default(0);
            $table->integer('quantity')->default(1);
            $table->text('handler')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 20, 8)->default(0);
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('basket_identifier');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->nullableMorphs('cartable'); // Allows polymorphic relations for different item types
            $table->string('name')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('price', 20, 8)->default(0);
            $table->integer('quantity')->default(1);
            $table->text('handler')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            $table->index('basket_identifier');
            $table->index(['basket_identifier', 'is_paid']);
        });

        Schema::create('cart_order_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_order_item_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 20, 8)->default(0);
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('cart_item_options');
        Schema::dropIfExists('cart_order_items');
        Schema::dropIfExists('cart_order_item_options');
    }
};
