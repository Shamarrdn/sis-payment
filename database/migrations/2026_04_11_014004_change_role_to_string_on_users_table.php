<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('role', 50)->default('student_affairs')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            // Reverting assuming string fallback for enum in sqlite
            $table->string('role')->default('student_affairs')->change();
        });
    }
};
