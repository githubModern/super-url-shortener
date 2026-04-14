<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('ban_type', ['none', 'soft', 'hard'])->default('none')->after('role');
            $table->timestamp('banned_at')->nullable()->after('ban_type');
            $table->timestamp('ban_expires_at')->nullable()->after('banned_at');
            $table->text('ban_reason')->nullable()->after('ban_expires_at');
            $table->foreignId('banned_by')->nullable()->after('ban_reason')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by']);
            $table->dropColumn(['ban_type', 'banned_at', 'ban_expires_at', 'ban_reason', 'banned_by']);
        });
    }
};
