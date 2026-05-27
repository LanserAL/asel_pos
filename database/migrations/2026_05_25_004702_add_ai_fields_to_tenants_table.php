<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('ai_provider', ['gemini', 'openai', 'claude'])->nullable()->after('status');
            $table->text('ai_api_key')->nullable()->after('ai_provider');
            $table->string('ai_model')->nullable()->after('ai_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['ai_provider', 'ai_api_key', 'ai_model']);
        });
    }
};
