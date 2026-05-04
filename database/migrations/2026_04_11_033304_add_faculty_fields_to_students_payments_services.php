<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Students: add faculty & department foreign keys ─────────────
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('college')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained()->nullOnDelete();
        });

        // ─── Payments: add snapshot columns ─────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            // stored as snapshot strings (not FK) so history is preserved even if faculty is renamed/deleted
            $table->string('faculty_snapshot')->nullable()->after('user_category');
            $table->string('department_snapshot')->nullable()->after('faculty_snapshot');
            // also keep FK for filtering
            $table->foreignId('faculty_id')->nullable()->after('department_snapshot')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained()->nullOnDelete();
        });

        // ─── Services: add faculty scope & applicable_to ─────────────────
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('is_active')->constrained()->nullOnDelete(); // null = all faculties
            $table->enum('applicable_to', ['all', 'students', 'graduates', 'military'])->default('all')->after('faculty_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['faculty_id', 'department_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['faculty_snapshot', 'department_snapshot', 'faculty_id', 'department_id']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropColumn(['faculty_id', 'applicable_to']);
        });
    }
};
