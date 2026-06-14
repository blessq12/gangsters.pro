<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

final class CLN_Client extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'CLN_clients';

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
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'consent_personal_data' => 'boolean',
        'consent_marketing' => 'boolean',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(CLN_ClientAddress::class, 'client_id');
    }
}
