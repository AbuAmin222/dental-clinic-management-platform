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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id')->unique()->nullable();
            $table->string('payment_method'); // 'visa', 'jawwal_pay', 'palpay'
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('ILS');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
