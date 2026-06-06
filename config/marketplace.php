<?php

return [
    'counties' => [
        'Nairobi', 'Mombasa', 'Kiambu', 'Nakuru', 'Kisumu', 'Machakos', 'Kajiado',
        'Uasin Gishu', 'Kilifi', 'Nyeri', 'Meru', 'Kakamega', 'Bungoma', 'Embu',
        'Murang\'a', 'Kericho', 'Nandi', 'Bomet', 'Trans Nzoia', 'Laikipia',
    ],

    'mpesa_paybill' => [
        'business_number' => env('MPESA_PAYBILL_NUMBER', '123456'),
        'account_prefix' => env('MPESA_PAYBILL_ACCOUNT', 'TREAD'),
    ],

    'bank_transfer' => [
        'bank_name' => env('BANK_NAME', 'Equity Bank'),
        'account_name' => env('BANK_ACCOUNT_NAME', 'TreadMart Ltd'),
        'account_number' => env('BANK_ACCOUNT_NUMBER', '0123456789'),
        'branch' => env('BANK_BRANCH', 'Nairobi'),
    ],
];
