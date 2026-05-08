<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->nullableMorphs('mailable'); // mailable_type, mailable_id
            $table->string('token')->nullable(); // unique token for tracking
            $table->string('identifier')->nullable();
            $table->string('from');
            $table->string('to');
            $table->string('subject');
            $table->json('lines');
            $table->json('table')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->json('attachments')->nullable();
            $table->json('data')->nullable();
            $table->string('theme')->default('default');
            $table->string('status')->default('pending');
            $table->boolean('display')->default(true);
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('identifier');
            $table->index('to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
