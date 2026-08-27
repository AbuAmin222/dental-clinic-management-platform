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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique(); // slug: 'invoices.create', 'appointments.cancel'
            $table->string('display_name');  // "إصدار فاتورة جديدة"
            $table->string('group');         // للتجميع في الواجهة: 'invoices', 'appointments', 'patients'

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
