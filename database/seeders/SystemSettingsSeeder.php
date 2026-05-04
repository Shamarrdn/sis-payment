<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\Discount;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Tuition group
            ['key' => 'tuition_base_amount',        'value' => '15000', 'type' => 'integer', 'label' => 'المصاريف الدراسية الأساسية (ج.م)',     'group' => 'tuition'],
            ['key' => 'tuition_extra_fee',           'value' => '30',    'type' => 'integer', 'label' => 'الرسوم الإدارية الإضافية (ج.م)',        'group' => 'tuition'],
            ['key' => 'tuition_full_amount',         'value' => '15030', 'type' => 'integer', 'label' => 'إجمالي الدفع الكامل (ج.م)',             'group' => 'tuition'],
            ['key' => 'tuition_installment_first',   'value' => '7530',  'type' => 'integer', 'label' => 'القسط الأول (ج.م)',                     'group' => 'tuition'],
            ['key' => 'tuition_installment_second',  'value' => '7500',  'type' => 'integer', 'label' => 'القسط الثاني (ج.م)',                    'group' => 'tuition'],

            // Payment group
            ['key' => 'payment_methods_enabled',     'value' => 'Visa,Fawry', 'type' => 'string', 'label' => 'وسائل الدفع المتاحة (فاصل بفاصلة)', 'group' => 'payment'],
            ['key' => 'payment_gateway_mode',        'value' => 'sandbox',    'type' => 'string', 'label' => 'وضع البوابة (sandbox / live)',        'group' => 'payment'],
            ['key' => 'receipt_footer_note',         'value' => 'احتفظ برقم الإيصال للمراجعة مع الشؤون المالية.', 'type' => 'string', 'label' => 'ملاحظة أسفل الإيصال', 'group' => 'payment'],

            // General group
            ['key' => 'university_name',             'value' => 'جامعة المستقبل للعلوم التكنولوجية', 'type' => 'string', 'label' => 'اسم الجامعة',     'group' => 'general'],
            ['key' => 'university_address',          'value' => 'القاهرة، مصر', 'type' => 'string', 'label' => 'عنوان الجامعة',                          'group' => 'general'],
            ['key' => 'support_email',               'value' => 'finance@university.edu.eg', 'type' => 'string', 'label' => 'البريد الإلكتروني للدعم',    'group' => 'general'],
            ['key' => 'support_phone',               'value' => '02-12345678', 'type' => 'string', 'label' => 'رقم التواصل للشؤون المالية',             'group' => 'general'],
        ];

        foreach ($settings as $s) {
            SystemSetting::firstOrCreate(
                ['key' => $s['key']],
                $s
            );
        }

        // Sample discounts
        $discounts = [
            [
                'name'                => 'إعفاء أبناء الشهداء والمعاقين',
                'category'            => 'Martyrs and disabled',
                'type'                => 'full',
                'value'               => 100,
                'approving_authority' => 'صندوق تكريم الشهداء',
                'notes'               => 'إعفاء كامل من المصاريف الدراسية بقرار وزارة التعليم العالي',
                'is_active'           => true,
            ],
            [
                'name'                => 'خصم النوابغ والمبدعين',
                'category'            => 'Genius',
                'type'                => 'percentage',
                'value'               => 50,
                'approving_authority' => 'وزارة التعليم العالي',
                'notes'               => 'خصم 50% لأصحاب الدرجات المتميزة',
                'is_active'           => true,
            ],
            [
                'name'                => 'خصم أبناء أعضاء هيئة التدريس',
                'category'            => 'Staff Children',
                'type'                => 'percentage',
                'value'               => 25,
                'approving_authority' => 'مجلس الجامعة',
                'notes'               => 'خصم 25% لأبناء العاملين بالجامعة',
                'is_active'           => true,
            ],
        ];

        foreach ($discounts as $d) {
            Discount::firstOrCreate(['name' => $d['name']], $d);
        }
    }
}
