<?php

return [
    /*
     * Configure tuition payment details here.
     * Full amount is 15030. Extra fee applies to one installment (or full payment).
     */
    'full_amount' => 15030,
    'extra_fee' => 30, // Admin/Application fee
    'extra_fee_applies_to' => 'first_installment',
    
    'installments' => [
        'first' => 7530,
        'second' => 7500,
    ],
];
