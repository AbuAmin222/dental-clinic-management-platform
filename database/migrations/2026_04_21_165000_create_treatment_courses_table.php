<?php

use App\Enums\TreatmentCourseStatus;
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
        Schema::create('treatment_courses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();

            $table->string('title');
            $table->unsignedTinyInteger('tooth_number')->nullable();
            $table->unsignedSmallInteger('planned_sessions_count')->nullable();
            $table->unsignedSmallInteger('completed_sessions_count')->default(0);
            $table->enum('status', TreatmentCourseStatus::values())->default(TreatmentCourseStatus::Ongoing->value);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status'], 'treatment_courses_patient_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_courses');
    }
};
