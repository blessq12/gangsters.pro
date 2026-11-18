<?php

namespace App\Infrastructure\Marketing\Model;

use Illuminate\Database\Eloquent\Model;

final class MKT_ContactBlock extends Model
{
    protected $table = 'MKT_contact_blocks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'phone',
        'email',
        'extra',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'extra' => 'array',
    ];
}
