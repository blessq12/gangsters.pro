<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\WorkShedule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use App\Models\User;
use Illuminate\Http\Request;


class MainController extends Controller
{
    public function __construct()
    {
        ViewFacade::share('company', Company::first());
        ViewFacade::share('currentDayShedule', WorkShedule::getCurrentDayShedule());
    }

    public function index()
    {
        return view('front.index', [
            'banner' => \App\Models\Banner::latest()->first(),
        ]);
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
        return view('front.contact', ['legals' => $legals,]);
    }

    public function purchaseAndDelivery(): View
    {
        return view('front.purchaseAndDelivery');
    }

    public function privacy(): View
    {
        return view('front.privacy');
    }

    public function loyalty(): View
    {
        return view('front.loyalty');
    }

    public function resetPassword(Request $request)
    {
        if (User::where('token_to_reset_password', $request->token)->exists()) {
            return view('front.reset-password', ['token' => $request->token]);
        }

        return redirect()->route('main.index')->with('error', 'Ссылка для сброса пароля недействительна');
    }
}
