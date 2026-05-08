<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->string('currency', 3)->primary();
            $table->string('display_name');
            $table->decimal('market_rate', 15, 8);
            $table->decimal('manual_rate', 15, 8)->nullable();
            $table->decimal('previous_rate', 15, 8)->nullable();
            $table->boolean('use_manual_rate')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
        });

        // Seed the table with default currencies
        foreach(config('currency.currencies') as $key => $currency) {
            DB::table('currencies')->insertOrIgnore([
                'currency' => $key,
                'display_name' => $currency['name'],
                'market_rate' => $currency['default_rate'] ?? 1,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
