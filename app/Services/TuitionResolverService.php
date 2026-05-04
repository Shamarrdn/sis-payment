<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TuitionConfig;
use Illuminate\Support\Carbon;

class TuitionResolverService
{
    /**
     * Resolve the correct tuition config for a student.
     *
     * Priority (most specific first):
     *   1. faculty + department + academic_year + user_category
     *   2. faculty + department + academic_year
     *   3. faculty + department
     *   4. faculty + academic_year + user_category
     *   5. faculty + academic_year
     *   6. faculty + user_category
     *   7. faculty only
     *   8. academic_year + user_category (global)
     *   9. academic_year only (global)
     *  10. user_category only (global)
     *  11. Catch-all global (all nulls)
     *
     * @return array{config: TuitionConfig|null, tuition: float, extra_fee: float, total: float, label: string, resolved_by: string}
     */
    /**
     * Map academic_year string to a year group (1-4).
     */
    private static function getYearGroup(string $academicYear): int
    {
        $map = [
            'الفرقة الأولى'  => 1,
            'الفرقة الثانية' => 2,
            'الفرقة الثالثة' => 3,
            'الفرقة الرابعة' => 4,
            'First Year'     => 1,
            'Second Year'    => 2,
            'Third Year'     => 3,
            'Fourth Year'    => 4,
            '1'              => 1,
            '2'              => 2,
            '3'              => 3,
            '4'              => 4,
        ];
        return $map[trim($academicYear)] ?? 1;
    }

    /**
     * Calculate installment amounts based on academic year group.
     * Year 1 & 2: total=15050, inst1=10000, inst2=5050
     * Year 3 & 4: total=20000, inst1=10000, inst2=10000
     */
    public static function getInstallmentAmounts(Student $student, float $total): array
    {
        $yearGroup = self::getYearGroup($student->academic_year ?? '');

        if ($yearGroup <= 2) {
            // Regardless of TuitionConfig, enforce the fixed split
            return [
                'inst1' => 10000.0,
                'inst2' => 5050.0,
                'total' => 15050.0,
            ];
        } else {
            return [
                'inst1' => 10000.0,
                'inst2' => 10000.0,
                'total' => 20000.0,
            ];
        }
    }

    public static function resolve(Student $student): array
    {
        $today = Carbon::today();

        $base = TuitionConfig::query()
            ->where('is_active', true)
            ->where('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $today);
            });

        $fid  = $student->faculty_id;
        $did  = $student->department_id;
        $year = $student->academic_year;
        $cat  = $student->user_category;

        $attempts = [
            // Most specific
            ['faculty_id' => $fid, 'department_id' => $did, 'academic_year' => $year, 'user_category' => $cat,  'label' => 'كلية + قسم + فرقة + فئة'],
            ['faculty_id' => $fid, 'department_id' => $did, 'academic_year' => $year, 'user_category' => null,  'label' => 'كلية + قسم + فرقة'],
            ['faculty_id' => $fid, 'department_id' => $did, 'academic_year' => null,  'user_category' => $cat,  'label' => 'كلية + قسم + فئة'],
            ['faculty_id' => $fid, 'department_id' => $did, 'academic_year' => null,  'user_category' => null,  'label' => 'كلية + قسم'],
            ['faculty_id' => $fid, 'department_id' => null, 'academic_year' => $year, 'user_category' => $cat,  'label' => 'كلية + فرقة + فئة'],
            ['faculty_id' => $fid, 'department_id' => null, 'academic_year' => $year, 'user_category' => null,  'label' => 'كلية + فرقة'],
            ['faculty_id' => $fid, 'department_id' => null, 'academic_year' => null,  'user_category' => $cat,  'label' => 'كلية + فئة'],
            ['faculty_id' => $fid, 'department_id' => null, 'academic_year' => null,  'user_category' => null,  'label' => 'كلية'],
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => $year, 'user_category' => $cat,  'label' => 'فرقة + فئة (عام)'],
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => $year, 'user_category' => null,  'label' => 'فرقة (عام)'],
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => null,  'user_category' => $cat,  'label' => 'فئة (عام)'],
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => null,  'user_category' => null,  'label' => 'الإعداد الافتراضي'],
        ];

        foreach ($attempts as $criteria) {
            $config = (clone $base)
                ->where('faculty_id',     $criteria['faculty_id'])
                ->where('department_id',  $criteria['department_id'])
                ->where('academic_year',  $criteria['academic_year'])
                ->where('user_category',  $criteria['user_category'])
                ->orderByDesc('effective_from')
                ->first();

            if ($config) {
                $tuition = (float) $config->tuition_amount;
                $discountAmount = 0.0;
                $discountName = null;

                // Apply active discount if special_category exists
                $discount = \App\Models\Discount::forCategory($student->special_category);
                if ($discount) {
                    $originalTuition = $tuition;
                    $tuition = $discount->applyTo($originalTuition);
                    $discountAmount = $originalTuition - $tuition;
                    $discountName = $discount->name;
                }

                $installments = self::getInstallmentAmounts($student, $tuition + $config->extra_fee);

                return [
                    'config'           => $config,
                    'original_tuition' => (float) $config->tuition_amount,
                    'tuition'          => $tuition,
                    'discount_amount'  => $discountAmount,
                    'discount_name'    => $discountName,
                    'extra_fee'        => (float) $config->extra_fee,
                    'total'            => $installments['total'],
                    'inst1_amount'     => $installments['inst1'],
                    'inst2_amount'     => $installments['inst2'],
                    'label'            => $criteria['label'],
                    'resolved_by'      => $config->label(),
                    'found'            => true,
                ];
            }
        }

        // Absolute fallback — use old config file if nothing found in DB
        $tuition = (float) config('tuition.full_amount', 15030);
        $discountAmount = 0.0;
        $discountName = null;

        $discount = \App\Models\Discount::forCategory($student->special_category);
        if ($discount) {
            $originalTuition = $tuition;
            $tuition = $discount->applyTo($originalTuition);
            $discountAmount = $originalTuition - $tuition;
            $discountName = $discount->name;
        }

        $installments = self::getInstallmentAmounts($student, $tuition);

        return [
            'config'           => null,
            'original_tuition' => $installments['total'],
            'tuition'          => $tuition,
            'discount_amount'  => $discountAmount,
            'discount_name'    => $discountName,
            'extra_fee'        => 0.0,
            'total'            => $installments['total'],
            'inst1_amount'     => $installments['inst1'],
            'inst2_amount'     => $installments['inst2'],
            'label'            => 'الإعداد الافتراضي (حسب الفرقة)',
            'resolved_by'      => 'ملف tuition.php',
            'found'            => false,
        ];
    }
}
