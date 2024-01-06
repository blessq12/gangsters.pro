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

        return view('front.index',[
            'goods' => ProductCategory::all()->each( fn ($item) => ($item->products)->each(function($e){
                $e->originalImage->setHidden(['type','image_type','created_at','updated_at','image_id']);
                $e->thumbMedium->setHidden(['type','image_type','created_at','updated_at','image_id']);
                $e->thumbSmall->setHidden(['type','image_type','created_at','updated_at','image_id']);
            })),
            'company' => Company::first(),
            'banners' => Banner::where('status', true)->get()->each(fn($item)=>$item->image),
            'stories' => Story::where('status', true)->get()->each( fn ($item) => $item->image )
        ]);
        
    }

    public function about(){
        return view('front.about');
    }

    public function contact(){
        return view('front.contact');
    }
}
