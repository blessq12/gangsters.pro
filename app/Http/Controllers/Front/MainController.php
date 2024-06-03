<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Mail\GreetingMessageWithPassword;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Encore\Admin\Facades\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MainController extends Controller
{
    public function __construct()
    {
        ViewFacade::share('company', Company::first());
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

    // Функция для изменения размера изображений
    public function resize()
    {
        $images = ProductImage::all();

        foreach ($images as $image) {
            $path = public_path('uploads/' . $image->path);
            if (File::exists($path)) {
                $img = \Image::make($path)->resize(1920, 1080, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode('jpg', 85); // Down quality to 75%
                $img->save($path);
            }
        }

        return 'all images resized and saved';
    }
    // Пакетное добавление артикулов для товаров
    public function addSKU()
    {
        $goods = Product::all();
        $goods->each(function ($item) {
            $item->sku = Str::sku('');
            $item->save();
        });

        return 'for all products added sku values';
    }
}
