<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->string('identifier')->primary();
            $table->string('marketplace_id')->nullable();
            $table->string('version')->nullable();
            $table->string('type');
            $table->string('name');
            $table->string('namespace')->unique();
            $table->string('status')->default('disabled');
            $table->timestamp('last_updated_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('extension_elements', function (Blueprint $table) {
            $table->id();
            $table->string('extension_identifier');
            $table->string('element');
            $table->string('view')->nullable();
            $table->string('permission')->nullable();
            $table->json('attributes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extensions');
        Schema::dropIfExists('extension_elements');
    }
};
