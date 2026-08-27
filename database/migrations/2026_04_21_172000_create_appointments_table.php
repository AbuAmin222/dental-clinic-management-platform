<?php

use App\Enums\AppointmentStatus;
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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('treatment_course_id')->nullable()
                ->constrained('treatment_courses')->nullOnDelete();

            $table->date('appointment_date');

            $table->time('start_time');
            $table->time('end_time')->nullable();

            $table->unsignedSmallInteger('duration_minutes')->default(config('clinic.appointments.default_duration_minutes', 30));

            $table->enum('status', AppointmentStatus::values())->default(AppointmentStatus::Scheduled->value);

            $table->text('reason_for_visit')->nullable();
            $table->text('doctor_notes')->nullable();


            $table->index(['doctor_id', 'appointment_date', 'status']);
            $table->unique(['doctor_id', 'appointment_date', 'start_time']);

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
