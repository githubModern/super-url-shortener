<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ads')) return;
        
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('format', ['banner', 'interstitial']);
            $table->text('content')->nullable(); // HTML or image URL
            $table->string('image_path')->nullable();
            $table->string('target_url')->nullable();
            $table->json('target_countries')->nullable(); // null = all countries
            $table->unsignedInteger('countdown_seconds')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('format');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
