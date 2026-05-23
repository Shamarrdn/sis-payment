<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category_group')->default('student_affairs')->after('type');
            $table->text('instructions')->nullable()->after('category_group');
            $table->json('required_fields')->nullable()->after('instructions');
            $table->unsignedSmallInteger('estimated_days')->default(3)->after('required_fields');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('fulfillment_status')->default('awaiting_processing')->after('status');
            $table->json('request_fields')->nullable()->after('notes');
            $table->timestamp('started_at')->nullable()->after('payment_date');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->timestamp('fulfillment_cancelled_at')->nullable()->after('completed_at');
            $table->unsignedTinyInteger('rating')->nullable()->after('fulfillment_cancelled_at');
            $table->string('rating_comment', 500)->nullable()->after('rating');
            $table->timestamp('rated_at')->nullable()->after('rating_comment');
        });

        Schema::create('payment_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->string('guard');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('email_or_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('success')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'last_login_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'last_login_at']);
        });
        Schema::dropIfExists('login_activities');
        Schema::dropIfExists('payment_status_histories');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_status', 'request_fields', 'started_at', 'completed_at',
                'fulfillment_cancelled_at', 'rating', 'rating_comment', 'rated_at',
            ]);
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['category_group', 'instructions', 'required_fields', 'estimated_days']);
        });
    }
};
