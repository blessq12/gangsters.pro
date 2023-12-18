<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('crm.banners.index', [
            'banners' => Banner::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('crm.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'header' => 'required|max:255',
            'subheader' => '',
            'image' => 'required|image'
        ]);
        $validated = (object)$validated;
        $banner = new Banner();
        $banner->header = $validated->header;
        $banner->subheader = $validated->subheader;
        
        if (!$banner->save()){
            return back()->withErrors(['create' => 'Не удалось создать баннер, попробуйте снова']);
        }

        Image::make($request->file('image'))
        ->resize(1920, 1080, fn($item)=>$item->aspectRatio())
        ->save('banners/banner-' .$banner->id .'.'.$request->file('image')->getClientOriginalExtension(), 90);
        ;

        $banner->image()->create([
            'path' => '/banners/banner-' .$banner->id .'.'.$request->file('image')->getClientOriginalExtension()
        ]);

        return back()->with('success', 'Баннер успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
