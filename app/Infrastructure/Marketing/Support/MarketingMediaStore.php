<?php

namespace App\Infrastructure\Marketing\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Сохранение картинок маркетинга на public disk.
 */
final class MarketingMediaStore
{
    public function storeBannerImage(int $bannerId, UploadedFile $file, string $slot): string
    {
        $slot = $slot === 'mobile' ? 'mobile' : 'desktop';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $basename = $slot.'-'.Str::uuid()->toString().'.'.$extension;
        $path = 'marketing/banners/'.$bannerId.'/'.$basename;
        Storage::disk('public')->put($path, $file->getContent());

        return $path;
    }

    public function storePromotionImage(int $promotionId, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $basename = Str::uuid()->toString().'.'.$extension;
        $path = 'marketing/promotions/'.$promotionId.'/'.$basename;
        Storage::disk('public')->put($path, $file->getContent());

        return $path;
    }
}
