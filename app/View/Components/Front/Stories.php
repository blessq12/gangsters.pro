<?php

namespace App\View\Components\Front;

use App\Models\Story;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Stories extends Component
{
    public $stories;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->stories = $this->getStories();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.stories', ['stories' => $this->stories]);
    }
    private function getStories()
    {
        $stories = Story::where('visible', true)->get();

        $stories->each(function ($story) {
            $story->thumb = '/uploads/' . $story->thumbnail('story');
            $story->image = '/uploads/' . $story->image;
        });
        $result = [];
        foreach ($stories as $story) {
            if (
                $story->start_time < now() &&
                $story->end_time > now()
            ) {
                $result[] = $story;
            } elseif (
                $story->end_time < now()
                && $story->non_hide
            ) {
                $result[] = $story;
            }
        }

        return $result;
    }
}
