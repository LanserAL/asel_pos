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
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->onDelete('cascade');
            $table->foreignId('opened_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('opening_amount', 10, 2)->default(0);
            $table->decimal('closing_amount', 10, 2)->nullable();
            $table->decimal('expected_amount', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamps();

            $table->index(['cash_register_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_sessions');
    }
};
