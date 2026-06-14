<?php

namespace Database\Seeders;

use App\Infrastructure\MarketingContent\Model\MKT_Banner;
use App\Infrastructure\MarketingContent\Model\MKT_Promotion;
use Illuminate\Database\Seeder;

class MarketingContentSeeder extends Seeder
{
    public function run(): void
    {
        MKT_Banner::query()->updateOrCreate(
            ['title' => 'Добро пожаловать в Gangsters'],
            [
                'description' => 'Свежие роллы и горячие блюда с доставкой по Томску.',
                'image_desktop' => '/images/banners/banner1.jpeg',
                'image_mobile' => '/images/banners/banner1.jpeg',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        MKT_Banner::query()->updateOrCreate(
            ['title' => 'Новинки меню'],
            [
                'description' => 'Следите за обновлениями — добавляем сезонные позиции.',
                'image_desktop' => '/images/banners/banner2.jpeg',
                'image_mobile' => '/images/banners/banner2.jpeg',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        MKT_Promotion::query()->updateOrCreate(
            ['title' => 'Бесплатная доставка'],
            [
                'body' => '<p>При заказе от определённой суммы доставка бесплатная. Условия уточняйте при оформлении.</p>',
                'image' => '/images/banners/banner3.jpeg',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        MKT_Promotion::query()->updateOrCreate(
            ['title' => 'Подарок к заказу'],
            [
                'body' => '<p>Добавьте роллы в корзину и получите подарок при достижении порога акции.</p>',
                'image' => '/images/banners/banner2.jpeg',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
    }
}
