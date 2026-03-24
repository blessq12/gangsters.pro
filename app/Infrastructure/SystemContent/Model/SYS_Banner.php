<?php

namespace App\Infrastructure\SystemContent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SYS_Banner extends Model
{
    use HasFactory;

    protected $table = 'banners';

    protected $guarded = [];
}

