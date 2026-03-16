<?php

namespace App\Infrastructure\Client\Model;

use App\Models\Order;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class UR_Client extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'UR_clients';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'password',
        'status',
        'consent_personal_data',
        'consent_marketing',
        'default_address_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'consent_personal_data' => 'bool',
        'consent_marketing' => 'bool',
    ];

    public function addresses(): HasMany
    {
        // Для админки нам нужны и удалённые адреса, поэтому withTrashed().
        return $this->hasMany(UR_ClientAddress::class, 'client_id')->withTrashed();
    }

    /**
     * Связь с доменными заказами клиента (ORD_orders).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Infrastructure\Order\Model\ORD_Order::class, 'client_id');
    }
}

