<?php

return [
    'currency' => env('MONEY_CURRENCY', 'NIO'),
    'storage_scale' => 2,
    'unit_scale' => 4,
    'calculation_scale' => 6,
    'rounding_mode' => 'HALF_UP',
    'max_integer_digits' => 14,
];
