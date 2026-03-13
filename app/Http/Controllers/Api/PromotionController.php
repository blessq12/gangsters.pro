<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    /**
     * Возвращает список системных акций для фронта.
     */
    public function index(): JsonResponse
    {
        $promotions = Promotion::query()
            ->orderBy('id')
            ->get()
            ->map(function (Promotion $promotion) {
                return [
                    'id' => $promotion->id,
                    'title' => $promotion->title,
                    'description' => $promotion->description,
                    'image' => $promotion->image
                        ? Storage::disk('media')->url($promotion->image)
                        : null,
                ];
            })
            ->values();

        return response()->json([
            'data' => $promotions,
        ]);
    }
}

