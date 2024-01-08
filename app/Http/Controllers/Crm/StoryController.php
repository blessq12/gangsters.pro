<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;



class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('crm.stories.index', [
            'stories' => Story::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $story = new Story();
        $story->name = $request->header;
        $image = Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();
        
        if(!is_dir(public_path('assets/stories'))){
            mkdir(public_path('assets/stories'));
        }

        if (!$story->save()) return back()->with('error','При сохранении возникла ошибка, попробуйте повторить');

        Image::make($request->file('image'))
        ->save(public_path('assets/stories/' . $image), 90);
        $story->image()->create([
            'type' => 'story',
            'path' => '/assets/stories/' . $image
        ]);

        return back()->with('success', 'Новая запись создана');
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
        $story = Story::findOrFail($id);

        $story->status = !$story->status;

        
        

        if ( !$story->update() ) {
            return back()->with('error', 'При обновлении произошла ошибка');
        }

        return back()->with('success', 'Запись успешно обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $story = Story::findOrFail($id);
        $image = $story->image;

        if (File::exists(public_path($image->path))){
            File::delete(public_path($image->path));
        }

        $image->delete();
        $story->delete();

        return back()->with('success', 'Запись успешно удалена');
    }
}
