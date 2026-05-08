<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('server_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('server');
            $table->string('external_id')->nullable();
            $table->string('username')->nullable();
            $table->text('password');
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index(['server', 'external_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('server_accounts');
    }
};
