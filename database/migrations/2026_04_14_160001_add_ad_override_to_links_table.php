<?php
// © Atia Hegazy — atiaeno.com

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            if (!Schema::hasColumn('links', 'ad_id')) {
                $table->foreignId('ad_id')->nullable()->after('custom_alias')->constrained('ads')->nullOnDelete();
            }
            if (!Schema::hasColumn('links', 'ad_override')) {
                $table->enum('ad_override', ['inherit', 'disable', 'force'])->default('inherit')->after('clicks_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            if (Schema::hasColumn('links', 'ad_id')) {
                $table->dropForeign(['ad_id']);
                $table->dropColumn('ad_id');
            }
        });
    }
};
