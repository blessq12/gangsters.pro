<?php

namespace App\Infrastructure\Client\Model;

use Illuminate\Database\Eloquent\Model;

final class CLN_PasswordResetToken extends Model
{
    public $timestamps = false;

    protected $table = 'CLN_password_reset_tokens';

    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
