<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Student Affairs User
        User::updateOrCreate(
            ['email' => 'student_affairs@uni.edu'],
            [
                'name' => 'موظف شئون الطلاب',
                'password' => Hash::make('password'),
                'role' => 'student_affairs',
            ]
        );

        // 2. Create Financial Affairs User
        User::updateOrCreate(
            ['email' => 'financial_affairs@uni.edu'],
            [
                'name' => 'موظف الشئون المالية',
                'password' => Hash::make('password'),
                'role' => 'financial_affairs',
            ]
        );

        // 5. Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@uni.edu'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 3. Create Sample Services - بيانات رسمية من مجلس الجامعة
        $officialServices = [
            ['name' => 'إستمارة التربية العسكرية', 'type' => 'خدمات عامة', 'amount' => 100, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'الإفادة بأنواعها', 'type' => 'شئون طلاب', 'amount' => 100, 'requires_subject' => false, 'sub_options' => ['إثبات قيد', 'بيان حالة شهادة حسن السير والسلوك', 'إفادة سحب ملف', 'شهادة فصل']],
            ['name' => 'الإلتماسات المقدمة من الطلاب عن نتائج الإمتحانات', 'type' => 'التماسات', 'amount' => 100, 'requires_subject' => true, 'sub_options' => null],
            ['name' => 'الطلاب المفصولين من كليات عند قيدهم بأقسام أو كليات أخرى', 'type' => 'شئون طلاب', 'amount' => 400, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'المحتوى العلمي باللغة العربية - الإنجليزية', 'type' => 'شئون طلاب', 'amount' => 300, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'بدل تالف شهادة تخرج مرحلة الدبلوم أو البكالوريوس', 'type' => 'شئون طلاب', 'amount' => 55, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'طلب الإعتذار عن دخول الإمتحانات', 'type' => 'شئون طلاب', 'amount' => 100, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'طلب سحب ملف / طلب إيقاف قيد / إعادة قيد', 'type' => 'شئون طلاب', 'amount' => 200, 'requires_subject' => false, 'sub_options' => ['طلب سحب ملف', 'طلب إيقاف قيد', 'طلب إعادة قيد']],
            ['name' => 'اشتراك السكة الحديد', 'type' => 'خدمات عامة', 'amount' => 20, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'بيان تقديرات باللغة العربية - الانجليزية', 'type' => 'شئون طلاب', 'amount' => 200, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'رسوم استخراج شهادة أصلية - الانجليزية', 'type' => 'شئون طلاب', 'amount' => 500, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'إعتماد صورة طبق الأصل', 'type' => 'خدمات عامة', 'amount' => 50, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'التصديق للتوثيق للخارجية', 'type' => 'خدمات عامة', 'amount' => 250, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'التحقق من صحة بيانات شهادة التخرج من داخل جمهورية مصر العربية', 'type' => 'شئون طلاب', 'amount' => 1000, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'الطلبات المقدمة لنقل قيد من الجامعة الى الجامعات الأخرى والعكس', 'type' => 'شئون طلاب', 'amount' => 400, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'رسوم عن المادة الواحدة للدور الثاني (سمر كورس)', 'type' => 'سمر كورس', 'amount' => 200, 'requires_subject' => true, 'sub_options' => null],
            ['name' => 'الكشف الطبي للطلاب المستجدين', 'type' => 'خدمات عامة', 'amount' => 220, 'requires_subject' => false, 'sub_options' => null],
            ['name' => 'خطاب تدريب', 'type' => 'شئون طلاب', 'amount' => 20, 'requires_subject' => false, 'sub_options' => null],
        ];

        foreach ($officialServices as $s) {
            Service::updateOrCreate(['name' => $s['name']], $s);
        }

        // ─── المصاريف الدراسية ───
        // الفرقة الأولى والثانية: إجمالي 15050 (ق1: 10000، ق2: 5050)
        // الفرقة الثالثة والرابعة: إجمالي 20000 (ق1: 10000، ق2: 10000)
        // المبالغ المخزنة هنا هي المبالغ الافتراضية — يتحكم TuitionResolverService بالمبلغ الفعلي حسب الفرقة
        Service::updateOrCreate(
            ['name' => 'المصاريف الدراسية (دفع كامل)'],
            [
                'type'             => 'مصاريف دراسية',
                'amount'           => 15050,
                'requires_subject' => false,
                'sub_options'      => null,
                'is_active'        => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'المصاريف الدراسية - القسط الأول'],
            [
                'type'             => 'مصاريف دراسية',
                'amount'           => 10000,
                'requires_subject' => false,
                'sub_options'      => null,
                'is_active'        => true,
            ]
        );

        Service::updateOrCreate(
            ['name' => 'المصاريف الدراسية - القسط الثاني'],
            [
                'type'             => 'مصاريف دراسية',
                'amount'           => 5050,
                'requires_subject' => false,
                'sub_options'      => null,
                'is_active'        => true,
            ]
        );

        $this->call(\Database\Seeders\FacultyDepartmentSeeder::class);
        $this->call(\Database\Seeders\CommunicationSeeder::class);

        // 4. Create Sample Student
        Student::create([
            'name' => 'أحمد محمد',
            'national_id' => '30001010101010',
            'reference_number' => 'REF12345',
            'academic_year' => 'الفرقة الثالثة',
            'program' => 'علوم الحاسب',
        ]);
    }
}
