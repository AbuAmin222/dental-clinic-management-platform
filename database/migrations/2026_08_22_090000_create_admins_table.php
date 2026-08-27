<?php

use App\Enums\AdminAccessLevel;
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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('employee_number')->unique();
            $table->enum('access_level', AdminAccessLevel::values())->default(AdminAccessLevel::Admin->value);
            $table->date('hiring_date')->nullable();

            // تتبع أمني خاص بحسابات المسؤولين — قيمة عالية لحساب صاحب صلاحيات هرمية كاملة.
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable(); // 45 = يستوعب IPv6 بالكامل

            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('access_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
