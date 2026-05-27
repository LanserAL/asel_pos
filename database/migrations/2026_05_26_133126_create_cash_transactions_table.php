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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_session_id')->constrained('cash_register_sessions')->onDelete('cascade');
            $table->string('type'); // in (ingress), out (egress)
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->timestamps();

            $table->index(['cash_register_session_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
