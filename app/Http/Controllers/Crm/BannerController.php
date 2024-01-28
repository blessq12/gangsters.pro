<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

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
        // 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $banner = new Banner($request->except('_token'));

        if (!$banner->save()) return back()->withErrors(['error'=>'Не удалось сохранить запись']);
        if (!is_dir(public_path('assets/banners'))) mkdir(public_path('assets/banners'));
        $image = Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();
        Image::make($request->file('image'))
        ->resize(1920, 1080, fn ($e) => $e->aspectRatio())
        ->save(public_path('assets/banners/') . $image, 90);

        $banner->image()->create([
            'path' => '/assets/banners/'.$image,
            'type' => 'banner'
        ]);

        return back()->with('success', 'Запись успешно создана');
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
    public function updateStatus(Request $request, string $id){
        $banner = Banner::findOrFail($id);
        $banner->status = !$banner->status;
        $banner->update();
        return back()->with('success', 'Статус баннера обновлен');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);
        $image = $banner->image;

        if (File::exists(public_path($image->path))){
            File::delete(public_path($image->path));
        }
        $image->delete();
        $banner->delete();

        return back()->with('success', 'Запись успешно удалена');
    }
}
