<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property mixed $birth_date
 * @property string $password_hash
 * @property bool $consent_personal_data
 * @property bool $consent_marketing
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class ClientModel extends Model
{
    use HasApiTokens;

    protected $table = 'CLI_clients';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'password_hash',
        'consent_personal_data',
        'consent_marketing',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
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
        return $this->hasMany(ClientAddressModel::class, 'client_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ClientFavoriteModel::class, 'client_id');
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }
}
