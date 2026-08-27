<?php

use App\Enums\SalaryPaymentStatus;
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
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('processed_by_financial_id')->nullable()
                ->constrained('financials')->nullOnDelete();

            $table->unsignedBigInteger('base_amount')->comment('Minor currency unit (agorot).');
            $table->unsignedBigInteger('deduction_amount')->default(0)->comment('Minor currency unit (agorot).');
            $table->unsignedBigInteger('bonus_amount')->default(0)->comment('Minor currency unit (agorot).');
            $table->unsignedBigInteger('amount')->comment('Net, computed: base - deduction + bonus. Minor currency unit (agorot).');

            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->enum('status', SalaryPaymentStatus::values())->default(SalaryPaymentStatus::Pending->value);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status'], 'salary_payments_user_status_index');
            $table->unique(['user_id', 'pay_period_start', 'pay_period_end'], 'salary_payments_unique_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
