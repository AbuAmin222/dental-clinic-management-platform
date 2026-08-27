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
        Schema::create('local_payment_methods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_id')->constrained('financials')->cascadeOnDelete();

            $table->string('title');
            $table->string('bank_phone_number')->nullable();
            $table->text('visa_card_number')->nullable()->comment('Encrypted at rest via Model encrypted cast.');
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('qr_code_path')->nullable();

            $table->boolean('is_visible_to_patient')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'is_visible_to_patient'], 'local_payment_methods_visibility_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_payment_methods');
    }
};
