<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;

class RawController extends Controller
{
    public function getCompany()
    {
        return response()->json(Company::first());
    }
    public function getLinks()
    {
        $routes = ['about', 'purchaseAndDelivery', 'contact', 'vacancy', 'privacy', 'loyalty'];
        $output = [];
        foreach ($routes as $route) {
            $output[] = [
                'name' => match ($route) {
                    'about' => 'О компании',
                    'purchaseAndDelivery' => 'Оплата и доставка',
                    'contact' => 'Контакты',
                    'vacancy' => 'Вакансии',
                    'privacy' => 'Политика конфиденциальности',
                    'loyalty' => 'Политика лояльности',
                },
                'url' => route('main.' . $route),
            ];
        }
        return response()->json($output);
    }
    public function getShedule()
    {
        $shedule = Company::first()->frontShedules();
        $shedule->each(function ($item) {
            $item->open_time = date('H:i', strtotime($item->open_time));
            $item->close_time = date('H:i', strtotime($item->close_time));
        });
        return response()->json($shedule);
    }
}
