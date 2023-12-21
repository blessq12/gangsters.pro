<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{
    public function store(Request $request){

        $id = $request->product_id;
        $count = 0;
        foreach ($request->file('images') as $image){
            $filename = 'product-' . $id .'-'. ++$count .'.' .$image->getClientOriginalExtension();
            Image::make($image)
            ->resize(1024, 1024, fn ($item) => $item->aspectRatio())
            ->save(public_path('products/' .$filename), 90);
        }

        return 'saved';
    }
    public function destroy(string $id){}


}
