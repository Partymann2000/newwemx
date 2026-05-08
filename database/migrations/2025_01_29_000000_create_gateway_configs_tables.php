<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_configs', function (Blueprint $table) {
            $table->id();
            $table->string('extension_identifier');
            $table->string('webhook_id')->nullable()->unique();
            $table->string('display_name')->nullable();
            $table->string('display_description')->nullable();
            $table->string('icon')->nullable();
            $table->string('namespace');
            $table->string('type')->default('payment');
            $table->longText('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_staff_only')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_configs');
    }
};
