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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_shipping_required')->default(false)->after('source');
            $table->text('shipping_address')->nullable()->after('is_shipping_required');
            $table->decimal('shipping_cost', 10, 2)->default(0.00)->after('shipping_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_shipping_required', 'shipping_address', 'shipping_cost']);
        });
    }
};
