<?php

namespace App\Support\Product;

final class TagColorLabels
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'amber' => 'Янтарный',
            'red' => 'Красный',
            'green' => 'Зелёный',
            'slate' => 'Серо-сланцевый',
            'sky' => 'Небесно-голубой',
            'violet' => 'Фиолетовый',
        ];
    }
}
