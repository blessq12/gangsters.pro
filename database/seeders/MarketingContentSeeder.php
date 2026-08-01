<?php

namespace Database\Seeders;

use App\Infrastructure\Content\Model\MKT_Banner;
use App\Infrastructure\Content\Model\MKT_Promotion;
use Illuminate\Database\Seeder;

class MarketingContentSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'image' => '/images/banners/banner1.jpeg',
                'sort_order' => 0,
            ],
            [
                'image' => '/images/banners/banner2.jpeg',
                'sort_order' => 1,
            ],
            [
                'image' => '/images/banners/banner3.jpeg',
                'sort_order' => 2,
            ],
        ];

        foreach ($banners as $banner) {
            MKT_Banner::query()->updateOrCreate(
                ['sort_order' => $banner['sort_order']],
                [
                    'image_desktop' => $banner['image'],
                    'image_mobile' => $banner['image'],
                    'is_active' => true,
                ],
            );
        }

        $promotions = [
            [
                'title' => 'Будни с бонусом 🍣',
                'body' => $this->htmlBody([
                    'В Gangsta Sushi даже будни вкусные.',
                    'Делай заказ от 1800 ₽ с понедельника по четверг — и получай ролл Лава в подарок.',
                ]),
                'image' => '/images/banners/banner1.jpeg',
                'sort_order' => 0,
            ],
            [
                'title' => 'День рождения по-гангстерски 🎉🍣',
                'body' => $this->htmlBody([
                    'Празднуй вместе с Gangsta Sushi.',
                    'Заказывай от 1000 ₽ — дарим ролл, а от 1800 ₽ — делаем скидку 15%.',
                    'Акция действует 3 дня до и 3 дня после твоего дня рождения — потому что один день для праздника слишком мало. 😎',
                ]),
                'image' => '/images/banners/banner2.jpeg',
                'sort_order' => 1,
            ],
            [
                'title' => 'Забери сам — забери больше 🍣',
                'body' => $this->htmlBody([
                    'В Gangsta Sushi уважают тех, кто двигается сам.',
                    'Оформляй заказ от 1000 ₽, забирай его сам — и получай ролл в подарок.',
                ]),
                'image' => '/images/banners/banner3.jpeg',
                'sort_order' => 2,
            ],
        ];

        foreach ($promotions as $promotion) {
            MKT_Promotion::query()->updateOrCreate(
                ['title' => $promotion['title']],
                [
                    'body' => $promotion['body'],
                    'image' => $promotion['image'],
                    'sort_order' => $promotion['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @param  list<string>  $paragraphs
     */
    private function htmlBody(array $paragraphs): string
    {
        return collect($paragraphs)
            ->map(fn (string $paragraph): string => '<p>'.e($paragraph).'</p>')
            ->implode('');
    }
}
