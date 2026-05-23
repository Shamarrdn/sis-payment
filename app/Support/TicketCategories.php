<?php

namespace App\Support;

class TicketCategories
{
    public const LABELS = [
        'payment'   => 'دفع ومصروفات',
        'data'      => 'بيانات شخصية',
        'service'   => 'خدمة إلكترونية',
        'login'     => 'تسجيل دخول',
        'documents' => 'مستندات',
        'other'     => 'أخرى',
    ];

    public static function keys(): array
    {
        return array_keys(self::LABELS);
    }

    public static function label(string $key): string
    {
        return self::LABELS[$key] ?? $key;
    }
}
