<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // e.g. "إعفاء أبناء الشهداء"
            $table->string('category');                     // maps to student special_category
            $table->enum('type', ['full', 'partial', 'percentage'])->default('percentage');
            $table->decimal('value', 8, 2)->default(0);    // percentage (0-100) or fixed amount
            $table->string('approving_authority')->nullable(); // e.g. "صندوق تكريم الشهداء"
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
