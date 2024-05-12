<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Mail\GreetingMessageWithPassword;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\UserRole;
use Encore\Admin\Facades\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MainController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        return view('front.index');
    }

    public function about()
    {
        return view('front.about');
    }
    public function vacancy()
    {
        return view('front.vacancy');
    }
    public function contact()
    {
        return view('front.contact');
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
