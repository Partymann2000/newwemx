<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('task');
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->boolean('show')->default(true);
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_task_logs');
    }
};
