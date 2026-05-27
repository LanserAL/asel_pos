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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // code, quantity, combo
            $table->string('code')->nullable();
            $table->string('discount_type'); // percentage, fixed_discount, fixed_price
            $table->decimal('discount_value', 10, 2);
            $table->integer('min_quantity')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->index(['tenant_id', 'type', 'status']);
            $table->index(['tenant_id', 'code']);
        });

        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
