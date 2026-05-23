<?php

namespace App\Support;

class ServiceCategoryGroups
{
    public const LABELS = [
        'educational'     => 'خدمات تعليمية',
        'financial'       => 'خدمات مالية',
        'documents'       => 'مستندات',
        'student_affairs' => 'شؤون طلاب',
    ];

    public static function keys(): array
    {
        return array_keys(self::LABELS);
    }

    public static function label(?string $key): string
    {
        return self::LABELS[$key] ?? ($key ?? '—');
    }
}
