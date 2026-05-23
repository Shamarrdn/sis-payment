<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentStatusHistory;
use App\Models\User;

class PaymentRequestService
{
    public static function initialize(Payment $payment): void
    {
        $service = $payment->service;
        $isTuition = $service && in_array($service->type, ['مصاريف دراسية', 'مصروفات دراسية'], true);

        $fulfillment = ($payment->status === 'paid' && !$isTuition)
            ? 'awaiting_processing'
            : ($payment->status === 'paid' ? 'completed' : 'awaiting_processing');

        $payment->update([
            'fulfillment_status' => $fulfillment,
            'completed_at'       => $fulfillment === 'completed' ? now() : null,
        ]);

        self::record($payment, 'created', 'تم إنشاء الطلب');
        if ($payment->status === 'paid') {
            self::record($payment, 'paid', 'تم استلام الدفع');
            self::record($payment, $fulfillment, PaymentStatusHistory::statusLabel($fulfillment));
        }
    }

    public static function record(Payment $payment, string $status, ?string $note = null, ?int $userId = null): void
    {
        PaymentStatusHistory::create([
            'payment_id' => $payment->id,
            'status'     => $status,
            'note'       => $note,
            'user_id'    => $userId,
        ]);
    }

    public static function start(Payment $payment, User $user): void
    {
        $payment->update([
            'fulfillment_status' => 'in_progress',
            'started_at'         => now(),
        ]);
        self::record($payment, 'in_progress', 'بدأ الموظف تنفيذ الطلب', $user->id);
    }

    public static function complete(Payment $payment, User $user): void
    {
        $payment->update([
            'fulfillment_status' => 'completed',
            'completed_at'       => now(),
        ]);
        self::record($payment, 'completed', 'تم إنجاز الخدمة', $user->id);
    }

    public static function cancelByStudent(Payment $payment): bool
    {
        if (!in_array($payment->fulfillment_status, ['awaiting_processing'], true)) {
            return false;
        }
        if ($payment->started_at) {
            return false;
        }

        $payment->update([
            'fulfillment_status'       => 'cancelled',
            'fulfillment_cancelled_at' => now(),
        ]);
        self::record($payment, 'cancelled', 'ألغى الطالب الطلب قبل بدء التنفيذ');

        return true;
    }

    public static function rate(Payment $payment, int $rating, ?string $comment = null): void
    {
        $payment->update([
            'rating'         => max(1, min(5, $rating)),
            'rating_comment' => $comment,
            'rated_at'       => now(),
        ]);
        self::record($payment, 'rated', 'تقييم الطالب: ' . $rating . '/5');
    }

    public static function isDelayed(Payment $payment): bool
    {
        if (!in_array($payment->fulfillment_status, ['awaiting_processing', 'in_progress'], true)) {
            return false;
        }
        $days = $payment->service?->estimated_days ?? 3;
        $since = $payment->started_at ?? $payment->payment_date ?? $payment->created_at;

        return $since && $since->copy()->addDays($days)->isPast();
    }

    public static function fulfillmentLabel(?string $status): string
    {
        return match ($status) {
            'awaiting_processing' => 'بانتظار التنفيذ',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => $status ?? '—',
        };
    }
}
