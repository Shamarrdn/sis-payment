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
            $table->string('phone')->nullable();
            $table->string('college')->default('جامعة بني سويف التكنولوجية');
            $table->string('user_category')->default('Student'); // Student, Graduate, Master's, etc.
            $table->string('special_category')->nullable(); // Martyrs, Outstanding, etc.
            $table->string('username')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('status')->default('active');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->boolean('allows_quantity')->default(false);
            $table->boolean('is_active')->default(true);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('program')->nullable();
            $table->string('user_category')->nullable();
            $table->decimal('amount', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['phone', 'college', 'user_category', 'special_category', 'username', 'password', 'status']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['category', 'allows_quantity', 'is_active']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'total_amount', 'payment_method', 'program', 'user_category']);
            $table->decimal('amount', 8, 2)->change();
        });
    }
};
