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
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('phone');
            $table->string('address')->nullable()->after('email');
        });

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'national_id', 'birth_certificate', 'personal_photo', 'additional'
            $table->string('file_path');
            $table->string('status')->default('pending'); // 'pending', 'verified', 'rejected'
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('sensitive_data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->json('requested_data'); // e.g., {"name":"...", "national_id":"...", "faculty_id":..., "department_id":...}
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('student_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the author/employee
            $table->text('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notes');
        Schema::dropIfExists('student_status_histories');
        Schema::dropIfExists('sensitive_data_requests');
        Schema::dropIfExists('student_documents');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['email', 'address']);
        });
    }
};
