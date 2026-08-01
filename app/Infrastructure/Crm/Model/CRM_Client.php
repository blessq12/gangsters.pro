<?php

namespace App\Infrastructure\Crm\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

final class CRM_Client extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'CRM_clients';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'password',
        'consent_personal_data',
        'consent_marketing',
        'addresses',
        'favorite_product_ids',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'consent_personal_data' => 'boolean',
        'consent_marketing' => 'boolean',
        'addresses' => 'array',
        'favorite_product_ids' => 'array',
    ];

    public function orderHistory(): HasMany
    {
        return $this->hasMany(CRM_OrderHistory::class, 'client_id');
    }

    public function getAuthPassword(): string
    {
        return (string) $this->getRawOriginal('password');
    }
}
