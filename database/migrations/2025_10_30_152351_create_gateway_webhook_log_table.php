<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('gateway_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_config_id')->constrained('gateway_configs')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('message')->nullable();
            $table->boolean('is_successful')->default(true);
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gateway_webhook_logs');
    }
};
