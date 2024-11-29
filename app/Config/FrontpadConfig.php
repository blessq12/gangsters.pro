<?php

namespace App\Config;

class FrontpadConfig
{
    public const MAX_RETRIES = 3;
    public const TIMEOUT = 10;
    public const CONNECT_TIMEOUT = 5;
    
    public const ORDER_STATUSES = [
        'NEW' => 1,
        'COMPLETED' => 10,
        'CANCELLED' => 11
    ];
    
    public const VALIDATION_RULES = [
        'name' => 'required|string|max:255',
        'tel' => 'required|string|max:20',
        'personQty' => 'required|integer|min:1',
        'items' => 'required|array|min:1',
        'items.*.sku' => 'required|integer',
        'items.*.qty' => 'required|integer|min:1',
    ];
} 