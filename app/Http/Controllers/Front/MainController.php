<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductImage;
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

    // public function resize()
    // {
    //     $images = ProductImage::all();
    //     foreach ($images as $image) {
    //         $path = public_path('uploads/' . $image->path);
    //         if (File::exists($path)) {
    //             $img = Image::make($path)->resize(1920, 1080, function ($constraint) {
    //                 $constraint->aspectRatio();
    //             })->encode('jpg', 85);
    //             $img->save($path);
    //         }
    //     }

    //     return 'all images resized and saved';
    // }

    // public function addSKU()
    // {
    //     $products = Product::all();
    //     $products->each(function ($product) {
    //         $product->sku = Str::sku('');
    //         $product->save();
    //     });

    //     return 'for all products added sku values';
    // }
}
