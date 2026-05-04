<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Service;
use App\Models\Payment;
use Carbon\Carbon;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        // Add Admin Accounts
        $admins = [
            ['name' => 'مدير النظام', 'email' => 'super@admin.com', 'role' => 'super_admin'],
            ['name' => 'شئون الطلاب', 'email' => 'student@affairs.com', 'role' => 'student_affairs'],
            ['name' => 'شئون الخريجين', 'email' => 'graduate@affairs.com', 'role' => 'graduate_affairs'],
            ['name' => 'إدارة مالية', 'email' => 'finance@affairs.com', 'role' => 'financial_affairs'],
        ];

        foreach ($admins as $admin) {
            User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make('password123'),
                    'role' => $admin['role'],
                ]
            );
        }

        // Add Services
        $servicesData = [
            ['name' => 'استخراج شهادة تخرج', 'type' => 'خدمات عامة', 'category' => 'Certificates', 'amount' => 150, 'allows_quantity' => true, 'is_active' => true],
            ['name' => 'إثبات قيد', 'type' => 'شئون طلاب', 'category' => 'Certificates', 'amount' => 50, 'allows_quantity' => true, 'is_active' => true],
            ['name' => 'بيان درجات', 'type' => 'شئون طلاب', 'category' => 'Transcript', 'amount' => 200, 'allows_quantity' => true, 'is_active' => true],
            ['name' => 'إخلاء طرف', 'type' => 'خدمات عامة', 'category' => 'Clearance', 'amount' => 100, 'allows_quantity' => false, 'is_active' => true],
            ['name' => 'بدل فاقد كارنيه', 'type' => 'شئون طلاب', 'category' => 'Replacement', 'amount' => 80, 'allows_quantity' => false, 'is_active' => true],
            ['name' => 'دفع كامل - مصاريف', 'type' => 'مصاريف دراسية', 'category' => 'Tuition', 'amount' => 15000, 'allows_quantity' => false, 'is_active' => true],
            ['name' => 'القسط الأول - مصاريف', 'type' => 'مصاريف دراسية', 'category' => 'Tuition', 'amount' => 7530, 'allows_quantity' => false, 'is_active' => true],
            ['name' => 'القسط الثاني - مصاريف', 'type' => 'مصاريف دراسية', 'category' => 'Tuition', 'amount' => 7500, 'allows_quantity' => false, 'is_active' => true],
        ];

        foreach ($servicesData as $serviceData) {
            Service::firstOrCreate(
                ['name' => $serviceData['name']],
                $serviceData
            );
        }

        // Add Sample Students
        $students = [
            ['name' => 'أحمد محمد علي السيد', 'national_id' => '29501010101010', 'reference_number' => 'REF001', 'academic_year' => 'الفرقة الثالثة', 'program' => 'حاسبات', 'user_category' => 'Student'],
            ['name' => 'سارة مصطفى كمال الدين', 'national_id' => '29602020202020', 'reference_number' => 'REF002', 'academic_year' => 'خريج', 'program' => 'إدارة أعمال', 'user_category' => 'Graduate'],
            ['name' => 'محمود ياسر فتحي', 'national_id' => '29703030303030', 'reference_number' => 'REF003', 'academic_year' => 'ماجستير', 'program' => 'هندسة', 'user_category' => 'Master’s'],
            ['name' => 'كريم مجدي حسين عبدالله', 'national_id' => '29804040404040', 'reference_number' => 'REF004', 'academic_year' => 'الفرقة الأولى', 'program' => 'الكليات العسكرية', 'user_category' => 'Military College'],
            ['name' => 'علي حسن محمود', 'national_id' => '29905050505050', 'reference_number' => 'REF005', 'academic_year' => 'الفرقة الثانية', 'program' => 'صيدلة', 'user_category' => 'Student', 'special_category' => 'Martyrs and disabled'],
        ];

        foreach ($students as $studentData) {
            $studentData['phone'] = '010' . rand(10000000, 99999999);
            $studentData['college'] = 'كلية التكنولوجيا';
            $studentData['password'] = Hash::make('12345678');
            $studentData['status'] = 'active';

            Student::firstOrCreate(
                ['national_id' => $studentData['national_id']],
                $studentData
            );
        }

        // Add some payments
        $firstStudent = Student::where('national_id', '29501010101010')->first();
        $transcriptService = Service::where('name', 'بيان درجات')->first();
        
        if ($firstStudent && $transcriptService) {
            Payment::firstOrCreate(
                ['reference_number' => 'PAY12345678'],
                [
                    'student_id' => $firstStudent->id,
                    'service_id' => $transcriptService->id,
                    'amount' => $transcriptService->amount,
                    'quantity' => 2,
                    'total_amount' => $transcriptService->amount * 2,
                    'payment_method' => 'Visa',
                    'academic_year' => $firstStudent->academic_year,
                    'program' => $firstStudent->program,
                    'user_category' => $firstStudent->user_category,
                    'notes' => 'نسختين عربي',
                    'payment_date' => Carbon::yesterday(),
                    'status' => 'paid'
                ]
            );
        }
    }
}
