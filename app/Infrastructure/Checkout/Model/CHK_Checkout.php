<?php

namespace App\Infrastructure\Checkout\Model;

use Illuminate\Database\Eloquent\Model;

final class CHK_Checkout extends Model
{
    protected $table = 'CHK_checkouts';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'status',
        'cart_snapshot',
        'client_snapshot',
        'delivery_snapshot',
        'payment_snapshot',
        'confirmed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cart_snapshot' => 'array',
            'client_snapshot' => 'array',
            'delivery_snapshot' => 'array',
            'payment_snapshot' => 'array',
            'confirmed_at' => 'datetime',
        ];
    }
}
