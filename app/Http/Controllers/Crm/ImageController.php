<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Image as ModelsImage;
use App\Models\Product;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function __construct(){
        if(!is_dir(public_path('assets/products'))){
            mkdir(public_path('assets/products'));
        }
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
            $uniq = Str::random(10);
            $name = time() .'-' . $product->id . '.' . $e->getClientOriginalExtension();
            Image::make($e)->resize(1024,1024, fn($item)=> $item->aspectRatio())->save( public_path('assets/products/original-') . $name , 95);
            Image::make($e)->resize(512,512, fn($item)=> $item->aspectRatio())->save(public_path('assets/products/thumb-medium-') . $name , 95);
            Image::make($e)->resize(128,128, fn($item)=> $item->aspectRatio())->save(public_path('assets/products/thumb-small-') . $name , 100);
            $product->images()->createMany([
                ['uniq'=> $uniq, 'type' => 'original', 'path' => '/assets/products/original-' .$name],
                ['uniq'=> $uniq, 'type' => 'thumb-medium', 'path' => '/assets/products/thumb-medium-' . $name],
                ['uniq'=> $uniq, 'type' => 'thumb-small', 'path' => '/assets/products/thumb-small-' . $name],
            ]);
        }
        
        return back()->with('success', 'Изображение(я) загружено');
    }
    public function storyImageUpload(Request $request){}
    public function bannerImageUpload(Request $request){}
    public function destroyImage(Request $request,string $id){
        if ($request->instance == 'product'){
            $uniq = ModelsImage::find($id)->uniq;
            $images = ModelsImage::where('uniq', $uniq)->get();
            foreach ($images as $e) {
                if (File::exists($e->path)){
                    File::delete($e->path);
                }
                $e->delete();
            }
            return back()->with('success', 'Фотография успешно удалена');
        }
        if ($request->instance == 'story'){}
        if ($request->instance == 'banner'){}
    }

}
