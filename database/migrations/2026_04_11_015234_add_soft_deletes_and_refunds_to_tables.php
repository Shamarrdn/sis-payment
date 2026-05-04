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
        Schema::table('students', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('services', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('users', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('payments', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('refund_status')->default('none'); // none, requested, approved, refunded, reversed, cancelled
            $table->text('refund_reason')->nullable();
            $table->decimal('refunded_amount', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['refund_status', 'refund_reason', 'refunded_amount']);
        });
        Schema::table('users', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('services', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('students', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};
