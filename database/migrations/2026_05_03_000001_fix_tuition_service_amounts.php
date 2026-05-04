<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix tuition service amounts:
     * - القسط الأول  → 10,000
     * - القسط الثاني → 5,050
     * - دفع كامل     → 15,050  (year 1&2 default)
     *
     * Note: actual per-student amounts are controlled dynamically
     * by TuitionResolverService based on academic_year.
     */
    public function up(): void
    {
        DB::table('services')
            ->where('name', 'like', '%القسط الأول%')
            ->where('type', 'مصاريف دراسية')
            ->update(['amount' => 10000]);

        DB::table('services')
            ->where('name', 'like', '%القسط الثاني%')
            ->where('type', 'مصاريف دراسية')
            ->update(['amount' => 5050]);

        DB::table('services')
            ->where('name', 'like', '%دفع كامل%')
            ->where('type', 'مصاريف دراسية')
            ->update(['amount' => 15050]);
    }

    public function down(): void
    {
        // Revert to old values
        DB::table('services')
            ->where('name', 'like', '%القسط الأول%')
            ->where('type', 'مصاريف دراسية')
            ->update(['amount' => 10050]);

        DB::table('services')
            ->where('name', 'like', '%القسط الثاني%')
            ->where('type', 'مصاريف دراسية')
            ->update(['amount' => 5000]);
    }
};
