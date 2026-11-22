<?php

namespace App\Infrastructure\Staff\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/** Физическая модель сотрудника Staff. */
final class STF_Employee extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $table = 'STF_employees';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'branch_id',
        'password_hash',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'branch_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }
}
