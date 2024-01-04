<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function __construct(){
        if(!is_dir(public_path('assets/products'))){
            mkdir(public_path('assets/products'));
        }
    }
    public function store(Request $request){

        if (!is_dir(storage_path('app/public/products'))){
            mkdir(storage_path('app/public/products'),0777);
        }

        $product = Product::findOrFail($request->product_id);

        foreach ($request->file('images') as $image){

            $filename = time() .'.' .$image->getClientOriginalExtension();

            Image::make($image)
            ->resize(1024, 1024, fn ($item) => $item->aspectRatio())
            ->save(public_path('products/' .$filename), 90);

            Image::make($image)
            ->resize(150, 150, fn ($item) => $item->aspectRatio())
            ->save(public_path('products/' . 'thumb-' .$filename), 90);

            $product->images()->createMany([
                ['path' => '/products/' .$filename , 'type' => 'image'],
                ['path' => '/products/' . 'thumb-' .$filename , 'type' => 'thumb']
            ]);
            
        }

        return back()->with('success', 'Фотографии добавлены');
    }
    public function productImageUpload(Request $request){

        $product = Product::findOrFail($request->prod_id);
        $images = $request->file('images');

        /**
         * full image 1024x1024
         * thumb medium 512x512
         * thumb small 128x128
         */
        foreach($images as $e){
            Image::make($e)->resize(1024,1024, fn($item)=> $item->aspectRatio());
            Image::make($e)->resize(512,512, fn($item)=> $item->aspectRatio());
            Image::make($e)->resize(128,128, fn($item)=> $item->aspectRatio());

        }
    }
    public function destroy(string $id){
        $image = ProductImage::findOrFail($id);
        if ( File::exists(public_path($image->path))) File::delete(public_path($image->path));
        $image->delete();
        return back()->with('success', 'Фотография удалена');
    }
}
