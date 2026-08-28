<?php

return [
    'default_register_code' => env('CASH_DEFAULT_REGISTER_CODE', 'CAJA-01'),
    'currency_code' => env('CASH_CURRENCY_CODE', 'NIO'),
    'currency_symbol' => env('CASH_CURRENCY_SYMBOL', 'C$'),
    'difference_tolerance' => env('CASH_DIFFERENCE_TOLERANCE', '0.00'),
    'difference_approval_threshold' => env('CASH_DIFFERENCE_APPROVAL_THRESHOLD', '100.00'),
    'withdrawal_approval_threshold' => env('CASH_WITHDRAWAL_APPROVAL_THRESHOLD', '10000.00'),
    'blind_closing' => env('CASH_BLIND_CLOSING', true),
    'timezone' => env('BUSINESS_TIMEZONE', 'America/Managua'),
    'max_denomination_quantity' => env('CASH_MAX_DENOMINATION_QUANTITY', 100000),
];
