<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->nullableMorphs('model', 'model');
            $table->string('event');
            $table->string('description')->nullable();
            $table->string('tag')->nullable();
            $table->string('field')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('properties')->nullable();
            $table->json('request')->nullable();
            $table->timestamps();

            $table->index('event');
            $table->index('tag');
            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
