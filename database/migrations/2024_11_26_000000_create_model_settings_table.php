<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('model_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->morphs('model');
            $table->timestamps();

            $table->unique(['key', 'model_id', 'model_type'], 'unique_setting');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_settings');
    }
};
