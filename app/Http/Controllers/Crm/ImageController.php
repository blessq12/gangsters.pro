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
    public function destroy(string $id){
        $image = ProductImage::findOrFail($id);
        if ( File::exists(public_path($image->path))) File::delete(public_path($image->path));
        $image->delete();
        return back()->with('success', 'Фотография удалена');
    }


}
