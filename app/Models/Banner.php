<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    /**
     * Таблица системных баннеров.
     *
     * Структура (на текущий момент):
     * - id
     * - image (string)        — путь к картинке баннера
     * - title (string|null)   — заголовок
     * - description (string|null) — описание/подзаголовок
     * - timestamps
     */
    protected $table = 'banners';

    protected $guarded = [];
}

