<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('server_connections', function (Blueprint $table) {
            $table->id();
            $table->string('extension_identifier');
            $table->string('alias')->unique();
            $table->text('short_description')->nullable();
            $table->longText('config')->nullable();
            $table->string('status')->default('unknown');
            $table->boolean('receive_alerts')->default(true);
            $table->string('alert_email')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('prevent_purchasing')->default(false); // prevent purchasing when server is inactive
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('server_connections');
    }
};
