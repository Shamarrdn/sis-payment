<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentStatusHistory extends Model
{
    protected $fillable = ['payment_id', 'status', 'note', 'user_id'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'created' => 'تم إنشاء الطلب',
            'paid' => 'تم الدفع',
            'awaiting_processing' => 'بانتظار التنفيذ',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'rated' => 'تم التقييم',
            default => $status,
        };
    }
}
