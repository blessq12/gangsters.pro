<?php

namespace Database\Seeders;

use App\Infrastructure\Marketing\Model\BannerModel;
use App\Infrastructure\Marketing\Model\PromotionMediaModel;
use App\Infrastructure\Marketing\Support\MarketingStoredPath;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Баннеры/акции из /api/content → MKT_*.
 */
class MarketingContentSeeder extends Seeder
{
    private const CONTENT_SOURCE_URL = 'https://gangsta-sushi.ru/api/content';

    public function run(): void
    {
        $payload = $this->fetchContentPayload();
        $marketing = is_array($payload['marketing'] ?? null) ? $payload['marketing'] : [];

        $banners = is_array($marketing['banners'] ?? null) ? $marketing['banners'] : [];
        $keptBannerIds = [];

        foreach (array_values($banners) as $sortOrder => $banner) {
            if (! is_array($banner)) {
                continue;
            }

            $id = (int) ($banner['id'] ?? 0);
            $desktop = MarketingStoredPath::normalizePublicDiskPath($banner['image_desktop'] ?? null);
            $mobile = MarketingStoredPath::normalizePublicDiskPath($banner['image_mobile'] ?? null);

            if ($desktop === null && $mobile === null) {
                continue;
            }

            $attributes = [
                'image_desktop' => $desktop,
                'image_mobile' => $mobile ?? $desktop,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ];

            if ($id > 0) {
                BannerModel::query()->updateOrCreate(['id' => $id], $attributes);
                $keptBannerIds[] = $id;
            } else {
                $row = BannerModel::query()->updateOrCreate(
                    ['sort_order' => $sortOrder],
                    $attributes,
                );
                $keptBannerIds[] = (int) $row->id;
            }
        }

        if ($keptBannerIds !== []) {
            BannerModel::query()->whereNotIn('id', $keptBannerIds)->delete();
        }

        $promotions = is_array($marketing['promotions'] ?? null) ? $marketing['promotions'] : [];
        $keptPromotionIds = [];

        foreach (array_values($promotions) as $sortOrder => $promotion) {
            if (! is_array($promotion)) {
                continue;
            }

            $id = (int) ($promotion['id'] ?? 0);
            $title = trim((string) ($promotion['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $attributes = [
                'title' => $title,
                'body' => $promotion['body'] ?? null,
                'image' => MarketingStoredPath::normalizePublicDiskPath($promotion['image'] ?? null),
                'sort_order' => $sortOrder,
                'is_active' => true,
            ];

            if ($id > 0) {
                PromotionMediaModel::query()->updateOrCreate(['id' => $id], $attributes);
                $keptPromotionIds[] = $id;
            } else {
                $row = PromotionMediaModel::query()->updateOrCreate(
                    ['title' => $title],
                    $attributes,
                );
                $keptPromotionIds[] = (int) $row->id;
            }
        }

        if ($keptPromotionIds !== []) {
            PromotionMediaModel::query()->whereNotIn('id', $keptPromotionIds)->delete();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchContentPayload(): array
    {
        $response = Http::timeout(60)
            ->acceptJson()
            ->retry(2, 500)
            ->get(self::CONTENT_SOURCE_URL);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Не удалось загрузить content: HTTP '.$response->status(),
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Некорректный формат content API.');
        }

        return $payload;
    }

}
