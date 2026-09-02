<?php

use App\Enums\Gender;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');

            $table->string('username')->unique();
            $table->string('email')->unique();

            $table->string('identity_number', 9)->unique();
            $table->string('phone');
            $table->timestamp('email_verified_at')->nullable();

            $table->string('phone_verification_code')->nullable();
            $table->timestamp('phone_verification_code_expires_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            $table->string('password');
            $table->boolean('must_change_password')->default(true);

            $table->unsignedBigInteger('base_salary')->nullable()->comment('Minor currency unit (agorot). Admin-managed only. See App\\Casts\\MoneyCast.');

            $table->enum('gender', Gender::values())->nullable();
            $table->date('date_of_birth');
            $table->string('address')->nullable();

            $table->boolean('is_active')->default(false);
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();

            $table->string('identity_photo_path')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();

            $table->index('is_active');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
