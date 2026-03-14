<?php

namespace App\Services\Slug;

use Illuminate\Support\Str;

final class TransliteratingSlugGenerator
{
    /** @var array<string, string> кириллица → латиница (ГОСТ 7.79-2000 / типовой для URL) */
    private const MAP = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
        'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
        'Я' => 'Ya',
        'і' => 'i', 'ї' => 'yi', 'є' => 'ye', 'ґ' => 'g', 'І' => 'I', 'Ї' => 'Yi', 'Є' => 'Ye', 'Ґ' => 'G',
    ];

    public function from(string $name): string
    {
        $transliterated = strtr($name, self::MAP);
        $slug = Str::slug($transliterated, '-', 'en');

        return $slug !== '' ? $slug : 'item';
    }

    /**
     * Уникальный slug для таблицы: при коллизии добавляется суффикс -2, -3 или -{id}.
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    public function uniqueFrom(string $name, string $modelClass, ?int $excludeId = null): string
    {
        $base = $this->from($name);
        $slug = $base;
        $n = 1;

        $query = $modelClass::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $n++;
            $slug = $base . '-' . $n;
            $query = $modelClass::where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
