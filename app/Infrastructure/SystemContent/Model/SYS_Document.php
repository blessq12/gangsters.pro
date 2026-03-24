<?php

namespace App\Infrastructure\SystemContent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SYS_Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'key',
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];
}

