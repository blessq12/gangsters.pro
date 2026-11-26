<?php

namespace App\Infrastructure\Staff\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * Физическая модель сотрудника Staff.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $role
 * @property int $branch_id
 * @property string $password_hash
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class EmployeeModel extends Authenticatable
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
