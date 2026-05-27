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
        Schema::create('payment_method_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('method_type', ['efectivo', 'transferencia', 'tarjeta']);
            $table->boolean('is_enabled')->default(true);
            $table->text('instructions')->nullable();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'method_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_settings');
    }
};
