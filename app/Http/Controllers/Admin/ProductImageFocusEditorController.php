<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Catalog\Model\PRD_ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class ProductImageFocusEditorController extends Controller
{
    public function show(PRD_ProductImage $image): View
    {
        $path = is_string($image->path) ? trim($image->path) : '';
        abort_if($path === '', 404);

        $disk = $image->disk ?: 'public';
        $imageUrl = Storage::disk($disk)->url($path);

        $hasFocus = $image->focus_x !== null && $image->focus_y !== null;

        return view('admin.product-image-focus-editor', [
            'image' => $image,
            'imageUrl' => $imageUrl,
            'focusX' => $hasFocus ? (float) $image->focus_x : 50.0,
            'focusY' => $hasFocus ? (float) $image->focus_y : 50.0,
            'hasFocus' => $hasFocus,
            'saveUrl' => route('filament.admin.product-image-focus-editor.save', ['image' => $image]),
        ]);
    }

    public function save(Request $request, PRD_ProductImage $image): JsonResponse
    {
        $validated = $request->validate([
            'focus_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'focus_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reset' => ['sometimes', 'boolean'],
        ]);

        if (($validated['reset'] ?? false) === true) {
            $image->focus_x = null;
            $image->focus_y = null;
            $image->save();

            return response()->json([
                'ok' => true,
                'focus_x' => null,
                'focus_y' => null,
            ]);
        }

        if (! array_key_exists('focus_x', $validated) || ! array_key_exists('focus_y', $validated)
            || $validated['focus_x'] === null || $validated['focus_y'] === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Нужна пара focus_x и focus_y.',
            ], 422);
        }

        $image->focus_x = round((float) $validated['focus_x'], 2);
        $image->focus_y = round((float) $validated['focus_y'], 2);
        $image->save();

        return response()->json([
            'ok' => true,
            'focus_x' => (float) $image->focus_x,
            'focus_y' => (float) $image->focus_y,
        ]);
    }
}
