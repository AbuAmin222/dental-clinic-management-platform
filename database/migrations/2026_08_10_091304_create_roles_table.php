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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique(); // Slug: 'admin', 'doctor', 'receptionist', 'head_financial'
            $table->string('display_name');  // للعرض: "طبيب معالج", "رئيس قسم المالية"
            $table->string('description')->nullable();

            // يحدد أي Model ينتمي له هذا الدور لخدمة ProfileModelFactory
            // القيم المسموحة: 'doctor', 'patient', 'receptionist', 'financial', null (للأدوار الإدارية البحث)
            $table->string('profile_type')->nullable();

            // منع حذف الأدوار الأساسية الخاصة بالنظام من قبل مسؤول النظام خطأً
            $table->boolean('is_system')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
