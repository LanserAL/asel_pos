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
            $table->json('currencies')->nullable()->after('plan_capacity');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->string('currency')->default('MXN')->after('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency')->default('MXN')->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('currencies');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
