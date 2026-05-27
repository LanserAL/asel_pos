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
        // 1. Customers Table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('regimen_fiscal')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->decimal('credit_balance', 12, 2)->default(0.00); // Current debt
            $table->integer('loyalty_points')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 2. Customer Credit Transactions Table
        Schema::create('customer_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('type', ['charge', 'payment']); // charge = consume credit, payment = abono to debt
            $table->decimal('amount', 12, 2);
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
        });

        // 3. Loyalty Transactions Table
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('type', ['earn', 'redeem']);
            $table->integer('points');
            $table->decimal('value_amount', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });

        // 4. Invoices Table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('uuid', 36)->unique();
            $table->string('series')->default('F');
            $table->string('folio');
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('customer_credit_transactions');
        Schema::dropIfExists('customers');
    }
};
