<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\TuitionConfig;
use App\Models\User;
use Illuminate\Support\Carbon;

class UniversityStructureSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Faculties ───────────────────────────────────────────────────
        $faculties = [
            [
                'name'        => 'كلية تكنولوجيا الصناعة والطاقة',
                'code'        => 'ENERGY',
                'description' => 'تضم أقسام الطاقة والسيارات والميكاترونيكس وتكنولوجيا المعلومات الصناعية',
            ],
            [
                'name'        => 'كلية تكنولوجيا المعلومات والحاسبات',
                'code'        => 'IT',
                'description' => 'تضم أقسام علوم الحاسب وهندسة البرمجيات والذكاء الاصطناعي وأمن المعلومات',
            ],
            [
                'name'        => 'كلية إدارة الأعمال التكنولوجية',
                'code'        => 'BUS',
                'description' => 'تضم أقسام إدارة الأعمال والمحاسبة وإدارة سلاسل التوريد',
            ],
        ];

        $facultyModels = [];
        foreach ($faculties as $f) {
            $facultyModels[$f['code']] = Faculty::firstOrCreate(['code' => $f['code']], array_merge($f, ['is_active' => true]));
        }

        // ─── Departments ─────────────────────────────────────────────────
        $departments = [
            'ENERGY' => [
                ['name' => 'تكنولوجيا الطاقة والبيئة',          'code' => 'ENERGY_ENV'],
                ['name' => 'تكنولوجيا السيارات',                'code' => 'AUTO'],
                ['name' => 'الميكاترونيكس',                     'code' => 'MECH'],
                ['name' => 'تكنولوجيا المعلومات الصناعية',       'code' => 'INDUS_IT'],
            ],
            'IT'     => [
                ['name' => 'علوم الحاسب والبرمجيات',             'code' => 'CS'],
                ['name' => 'الذكاء الاصطناعي وتعلم الآلة',       'code' => 'AI'],
                ['name' => 'أمن المعلومات والشبكات',             'code' => 'SEC'],
                ['name' => 'هندسة البيانات والسحابة',            'code' => 'DATA'],
            ],
            'BUS'    => [
                ['name' => 'إدارة الأعمال الرقمية',              'code' => 'BIZ'],
                ['name' => 'محاسبة وتدقيق',                     'code' => 'ACC'],
                ['name' => 'إدارة سلاسل التوريد',               'code' => 'SCM'],
            ],
        ];

        foreach ($departments as $facultyCode => $depts) {
            $faculty = $facultyModels[$facultyCode];
            foreach ($depts as $d) {
                Department::firstOrCreate(
                    ['faculty_id' => $faculty->id, 'code' => $d['code']],
                    array_merge($d, ['faculty_id' => $faculty->id, 'is_active' => true])
                );
            }
        }

        // ─── Tuition Configs ─────────────────────────────────────────────
        // Global default for all students (fallback)
        $configs = [
            // Global defaults (applies when no specific config matches)
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => 'الفرقة الأولى',   'user_category' => 'Student', 'tuition_amount' => 15000, 'extra_fee' => 30],
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => 'الفرقة الثانية',  'user_category' => 'Student', 'tuition_amount' => 15000, 'extra_fee' => 30],
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => 'الفرقة الثالثة', 'user_category' => 'Student', 'tuition_amount' => 20000, 'extra_fee' => 30],
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => 'الفرقة الرابعة',  'user_category' => 'Student', 'tuition_amount' => 20000, 'extra_fee' => 30],
            // Graduate global
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => null,              'user_category' => 'Graduate', 'tuition_amount' => 5000,  'extra_fee' => 0],
            // Master's
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => null,              'user_category' => "Master's", 'tuition_amount' => 25000, 'extra_fee' => 50],
            // Military
            ['faculty_id' => null, 'department_id' => null, 'academic_year' => null,              'user_category' => 'Military College', 'tuition_amount' => 10000, 'extra_fee' => 0],
        ];

        // IT faculty specific (higher fees)
        $itFaculty = $facultyModels['IT'];
        $configs[] = ['faculty_id' => $itFaculty->id, 'department_id' => null, 'academic_year' => 'الفرقة الأولى',   'user_category' => 'Student', 'tuition_amount' => 17000, 'extra_fee' => 30];
        $configs[] = ['faculty_id' => $itFaculty->id, 'department_id' => null, 'academic_year' => 'الفرقة الثانية',  'user_category' => 'Student', 'tuition_amount' => 17000, 'extra_fee' => 30];
        $configs[] = ['faculty_id' => $itFaculty->id, 'department_id' => null, 'academic_year' => 'الفرقة الثالثة', 'user_category' => 'Student', 'tuition_amount' => 22000, 'extra_fee' => 30];
        $configs[] = ['faculty_id' => $itFaculty->id, 'department_id' => null, 'academic_year' => 'الفرقة الرابعة',  'user_category' => 'Student', 'tuition_amount' => 22000, 'extra_fee' => 30];

        $superAdmin = User::where('role', 'super_admin')->first();

        foreach ($configs as $cfg) {
            TuitionConfig::firstOrCreate(
                [
                    'faculty_id'    => $cfg['faculty_id'],
                    'department_id' => $cfg['department_id'] ?? null,
                    'academic_year' => $cfg['academic_year'],
                    'user_category' => $cfg['user_category'],
                ],
                array_merge($cfg, [
                    'effective_from' => Carbon::parse('2025-09-01'),
                    'effective_to'   => null,
                    'is_active'      => true,
                    'updated_by'     => $superAdmin?->id,
                    'notes'          => 'إعداد افتراضي — السنة الدراسية 2025-2026',
                ])
            );
        }
    }
}
