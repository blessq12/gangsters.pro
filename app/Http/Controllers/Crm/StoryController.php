<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;


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
    public function create()
    {
        return view('crm.stories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'thumb' => 'required|image',
            'image' => 'required|image'
        ]);
        $validated = (object) $validated;
        $story = new Story();
        $story->name = $validated->name;
        if (!$story->save()){
            return back()->withErrors(['create' => 'Ошибка при создании']);
        }
        // images
        Image::make($request->file('thumb'))
        ->resize(350, 200, fn($img)=>$img->aspectRatio())
        ->save(public_path('/stories/thumb_' . $story->id . '.' .$request->file('thumb')->getClientOriginalExtension()),80);

        Image::make($request->file('image'))
        ->resize(1080, 1920, fn($img)=>$img->aspectRatio())
        ->save('stories/image_' .$story->id . '.' .$request->file('image')->getClientOriginalExtension(),80);

        $story->update([
            'thumb' => '/stories/thumb_' .$story->id . '.' .$request->file('thumb')->getClientOriginalExtension(),
            'image' => '/stories/image_' .$story->id . '.' .$request->file('image')->getClientOriginalExtension()
        ]);
        if ($story->save()){
            return back()->with('success', 'Успешно создано');
        }
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
