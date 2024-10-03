<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\WorkShedule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Str;

use App\Facades\Frontpad;
use Intervention\Image\Facades\Image;


class MainController extends Controller
{
    public function __construct()
    {
        ViewFacade::share('company', Company::first());
        ViewFacade::share('currentDayShedule', WorkShedule::getCurrentDayShedule());
        ViewFacade::share('links', [
            (object) ['name' => 'О компании', 'route' => 'main.about'],
            (object) ['name' => 'Оплата и доставка', 'route' => 'main.purchaseAndDelivery'],
            (object) ['name' => 'Контакты', 'route' => 'main.contact'],
        ]);
    }

    public function index()
    {
        return view('front.index');
    }

    public function vacancy()
    {
        return view('front.vacancy');
    }

    public function about()
    {
        return view('front.about');
    }

    public function contact()
    {
        $company = Company::first();
        $legals = $company->legals()->first()->toArray();
        $legals = [
            'ИНН' => $legals['inn'],
            'ОГРН' => $legals['ogrn'],
            'ОКПО' => $legals['okpo'],
            'КПП' => $legals['kpp'],
            'Директор' => $legals['owner'],
            'Электронная почта' => $legals['legal_email'],
            'Форма собственности' => $legals['legal_form'],
        ];
        return view(
            'front.contact',
            [
                'legals' => $legals,
            ]
        );
    }

    public function purchaseAndDelivery(): View
    {
        return view('front.purchaseAndDelivery');
    }

    public function privacy(): View
    {
        return view('front.privacy');
    }
}
