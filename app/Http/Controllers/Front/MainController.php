<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\Story;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index(){
        $company = Company::first();
        $company->legals;
        return view('front.index',[
            'goods' => ProductCategory::all()->each( fn ($item) => $item->products ),
            'company' => $company,
            'banners' => Banner::all(),
            'stories' => Story::all()
        ]);
    }

    public function about(){
        return view('front.about');
    }

    public function contact(){
        return view('front.contact');
    }
}
