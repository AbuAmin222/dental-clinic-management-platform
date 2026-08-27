<?php

use App\Enums\PaymentTransactionStatus;
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

            $table->string('transaction_reference')->unique()->nullable();

            $table->foreignId('local_payment_method_id')->nullable()->constrained('local_payment_methods')->nullOnDelete();

            $table->string('payment_method');

            $table->unsignedBigInteger('amount')->default(0);

            $table->string('currency')->default('ILS');

            $table->enum('status', PaymentTransactionStatus::values())->default(PaymentTransactionStatus::Pending->value);

            $table->json('gateway_response')->nullable();

            $table->string('proof_image_path')->nullable();

            $table->text('notes')->nullable();

            $table->index(['invoice_id', 'status']);

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
