<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('name')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
