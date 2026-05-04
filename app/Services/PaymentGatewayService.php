<?php

namespace App\Services;

class PaymentGatewayService
{
    /**
     * Process a fake payment transaction.
     * 
     * @param float $amount
     * @param string $method Visa, Fawry, Cash
     * @return array
     */
    public function processPayment(float $amount, string $method): array
    {
        // Simulate external API call
        $success = match($method) {
            'Visa' => (rand(1, 100) > 10), // 90% success rate
            'Fawry' => true, // Always return pending/success logic
            'Cash' => true,
            default => false,
        };

        if ($success) {
            return [
                'status' => 'paid',
                'reference_number' => 'REF-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'message' => 'Payment processed successfully.',
            ];
        } else {
            return [
                'status' => 'failed',
                'reference_number' => 'REF-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'message' => 'Payment failed or declined.',
            ];
        }
    }
}
