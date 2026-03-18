<?php

namespace App\Application\YandexFood\Presenter;

use App\Models\Company;

class YandexRestaurantPresenter
{
    public function present(Company $company): array
    {
        return [
            'places' => [
                [
                    'id' => (string) $company->id,
                    'title' => $company->name,
                    'address' => sprintf(
                        '%s, %s, %s',
                        $company->city,
                        $company->street,
                        $company->house,
                    ),
                ],
            ],
        ];
    }
}

