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
        return $this->hasMany(UR_ClientAddress::class, 'client_id');
    }

    /**
     * Заглушка-связь заказов клиента по legacy-полю user_id.
     * Позже можно будет перевесить на doman-specific связь.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}

