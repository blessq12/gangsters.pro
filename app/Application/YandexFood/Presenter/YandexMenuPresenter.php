<?php

namespace App\Application\YandexFood\Presenter;

class YandexMenuPresenter
{
    public function presentComposition(array $categories, array $products): array
    {
        return [
            'categories' => $categories,
            'items' => $products,
        ];
    }
}

