<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_configs', function (Blueprint $table) {
            $table->id();
            // nullable = applies to ALL faculties/departments/years/categories
            $table->foreignId('faculty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('academic_year')->nullable();    // e.g. "الفرقة الأولى" | null = all years
            $table->string('user_category')->nullable();   // Student | Graduate | Military | null = all
            $table->decimal('tuition_amount', 10, 2);
            $table->decimal('extra_fee', 8, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_configs');
    }
};
