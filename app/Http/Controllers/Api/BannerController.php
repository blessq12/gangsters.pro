<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Возвращает список системных баннеров для фронта.
     */
    public function index(): JsonResponse
    {
        $banners = Banner::query()
            ->orderBy('id')
            ->get()
            ->map(function (Banner $banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'image' => $banner->image
                        ? Storage::disk('media')->url($banner->image)
                        : null,
                ];
            })
            ->values();

        return response()->json([
            'data' => $banners,
        ]);
    }
}

