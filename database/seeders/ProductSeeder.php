<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    protected $categories = [
        'roll' => 1,
        'baked-roll' => 2,
        'tempura-roll' => 3,
        'vegan-roll' => 4,
        'mexico' => 5,
        'wok' => 6,
        'soup' => 7,
        'snack' => 8,
        'kit' => 9,
        'drink' => 10,
        'salad' => 11
    ];

    public function run(): void
    {
        DB::table('products')->insert([
            // Roll
            ['product_category_id' => $this->categories['roll'], 'name' => 'Цезарь Ролл', 'weight' => 255, 'price' => 315, 'consist' => 'Сливочный сыр, копченая куриная грудка, зеленый лук, помидор, сыр Чеддер, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Фудзияма', 'weight' => 450, 'price' => 375, 'consist' => 'Сливочный сыр, болгарский перец,  креветка в чесночном соусе'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Филадельфия с огурцом', 'weight' => 305, 'price' => 480, 'consist' => 'Сливочный сыр, лосось, огурчик'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Филадельфия с зеленым луком', 'weight' => 305, 'price' => 510, 'consist' => 'Сливочный сыр, лосось, зеленый лучок'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Филадельфия с авокадо', 'weight' => 305, 'price' => 510, 'consist' => 'Сливочный сыр, лосось, авокадо'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Филадельфия Лайт Эби', 'weight' => 250, 'price' => 429, 'consist' => 'Сливочный сыр, креветка, лосось, соус, кунжут'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Филадельфия лайт', 'weight' => 255, 'price' => 360, 'consist' => 'Сливочный сыр, лосось, огурчик'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Филадельфия классик', 'weight' => 300, 'price' => 489, 'consist' => 'Сливочный сыр, лосось'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Унаги Филадельфия', 'weight' => 235, 'price' => 419, 'consist' => 'Сливочный сыр, огурчик, копченый угорь, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Унаги Маки', 'weight' => 190, 'price' => 315, 'consist' => 'Ролл с угрем'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Унаги Авокадо', 'weight' => 235, 'price' => 250, 'consist' => 'Ролл с авокадо и угрем'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Сяке-Маки с зелёным лучком', 'weight' => 210, 'price' => 330, 'consist' => 'Рис, лосось, зеленый лучок'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Сяке-Маке', 'weight' => 205, 'price' => 320, 'consist' => 'Ролл с лососем'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Сливочный лосось', 'weight' => 205, 'price' => 315, 'consist' => 'Сливочный сыр, лосос'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Санни Корлеоне', 'weight' => 235, 'price' => 395, 'consist' => 'Сливочный сыр, лосось, болгарский перец,  икра летучей рыбы, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Ролл Нежный', 'weight' => 270, 'price' => 315, 'consist' => 'Рис, мясо краба, зеленый лучок, творожный сыр'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Пьяная клюква', 'weight' => 240, 'price' => 350, 'consist' => 'Сливочный сыр, обжареный лосось, зеленый лучок, клюква, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Острый лосось', 'weight' => 300, 'price' => 395, 'consist' => 'Сливочный сыр, огурчик, тартар из лосося, фирменный соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Норвегия', 'weight' => 240, 'price' => 429, 'consist' => 'Сливочный сыр, лосось, копченый угорь, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Небраска', 'weight' => 195, 'price' => 315, 'consist' => 'Сливочный сыр, лосось  х/к, зеленый лук'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Наоми', 'weight' => 220, 'price' => 315, 'consist' => 'Сливочный сыр, лосось х/к, помидор, зеленый лучок, соус спайси'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Намиби Эби Маки', 'weight' => 260, 'price' => 419, 'consist' => 'Сливочный сыр, лосось, огурчик, креветка, острый соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Мэри Корлеоне', 'weight' => 310, 'price' => 360, 'consist' => 'Сливочный сыр, семга х/к, огурчик, кунжут, микро зелень амаранта'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Мозаика Маки', 'weight' => 220, 'price' => 350, 'consist' => 'Сливочный сыр, тигровая креветка, микс икры летучей рыбы'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Мелани', 'weight' => 250, 'price' => 385, 'consist' => 'Ролл с копченой рыбкой, авокадо, и сливочным сыром'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Мексиканский ролл с курочкой', 'weight' => 185, 'price' => 310, 'consist' => 'Сырная лепешка, копченая курочка, сливочный сыр, томат, болгарский перец'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Мексиканский ролл с копченой рыбкой', 'weight' => 185, 'price' => 335, 'consist' => 'Сырная лепешка, сливочный сыр, томат, зеленый лук, лосось  х/к'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Майкл Корлеоне', 'weight' => 310, 'price' => 555, 'consist' => 'Сливочный сыр, лосось, икра, микро зелень рукколы/укроп'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Луиза', 'weight' => 315, 'price' => 430, 'consist' => 'Сливочный сыр, зеленый лучок, краб, угорь'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Лава с огурцом', 'weight' => 290, 'price' => 250, 'consist' => 'Сливочный сыр, огурчик, соус Лава'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Лава с лососем Терияки', 'weight' => 290, 'price' => 315, 'consist' => 'Сливочный сыр, лосось обжаренный в соусе Терияки, соус Лава'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Лава с креветкой', 'weight' => 290, 'price' => 325, 'consist' => 'Сливочный сыр, креветка, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Лава с крабом', 'weight' => 290, 'price' => 295, 'consist' => 'Сливочный сыр, мясо краба, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Лава Luxe', 'weight' => 390, 'price' => 555, 'consist' => 'Сливочный сыр, лосось, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Лава', 'weight' => 290, 'price' => 325, 'consist' => 'Сливочный сыр, лосось, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Клайд', 'weight' => 280, 'price' => 315, 'consist' => 'Сливочный сыр, копченая курочка, зеленый лучок, тобика, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Кани Кунсей Маки', 'weight' => 215, 'price' => 315, 'consist' => 'Сливочный сыр, лосось х/к, мясо краба, лучок'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Калифорния с рыбкой', 'weight' => 250, 'price' => 370, 'consist' => 'Сливочный сыр, краб, лосось, огурчик, икра летучей рыбы'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Калифорния', 'weight' => 250, 'price' => 370, 'consist' => 'Сливочный сыр, краб, креветка, огурчик, икра летучей рыбы'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Инь-Янь Маки', 'weight' => 185, 'price' => 315, 'consist' => 'Копченый угорь, лосось, икра летучей рыбы'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Ева', 'weight' => 300, 'price' => 430, 'consist' => 'Сливочный сыр, такуан, угорь'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Дракон Маки', 'weight' => 240, 'price' => 429, 'consist' => 'Сливочный сыр, лосось обжареный в соусе Терияки, копченый угорь, соус унаги, кунжут'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Джоржия', 'weight' => 280, 'price' => 395, 'consist' => 'Сливочный сыр, лосось, мясо краба, зеленый лучок, соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Гейша с креветкой', 'weight' => 220, 'price' => 315, 'consist' => 'Креветка, сливочный сыр, огурец'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Гейша', 'weight' => 220, 'price' => 315, 'consist' => 'Лосось, сливочный сыр, огурец'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Восходящее солнце', 'weight' => 225, 'price' => 320, 'consist' => 'Сливочный сыр, икра летучей рыбы, тигровая креветка, огурчик'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Вито Корлеоне', 'weight' => 250, 'price' => 385, 'consist' => 'Лосось обжаренный в соусе терияки, огурчик, сливочный сыр, икра'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Бонни', 'weight' => 260, 'price' => 315, 'consist' => 'Сливочный сыр, обжаренный лосось, помидор, зеленый лучок,  соус'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Беби Эби', 'weight' => 336, 'price' => 555, 'consist' => 'Креветка, сливочный сыр, икра летучей рыбы, болгарский перец, соус Спайси'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Аполлония Корлеоне', 'weight' => 235, 'price' => 400, 'consist' => 'Лосось, творожный сыр, зеленый лучок, соус, икра летучей рыбы'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Аляска', 'weight' => 225, 'price' => 315, 'consist' => 'Лосось, огурчик, сливочный сыр, кунжут'],
            ['product_category_id' => $this->categories['roll'], 'name' => 'Lucky', 'weight' => 200, 'price' => 250, 'consist' => 'Сливочный сыр, лосось, авокадо, красный лук,  соус'],
            // Baked roll
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Эби Черри Hot', 'weight' => 270, 'price' => 345, 'consist' => 'Сливочный сыр, томат, тигровая креветка, соус спайси'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Эби Спайси Hot', 'weight' => 245, 'price' => 345, 'consist' => 'Сливочный сыр, креветка, огурчик, острый соус'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Унаги Hot', 'weight' => 240, 'price' => 350, 'consist' => 'Сливочный сыр, копченый угорь, зелёный лучок, огурец, соус, кунжу'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Тори Hot', 'weight' => 230, 'price' => 335, 'consist' => 'Сливочный сыр, томат, копченая куриная грудка, соус'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Тигр Hot', 'weight' => 275, 'price' => 345, 'consist' => 'Сливочный сыр, тигровая креветка темпура, соус Спайси'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Тацуми', 'weight' => 450, 'price' => 375, 'consist' => 'Сливочный сыр, креветка, сырно-чесночная шапочка'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Сяке Спайси Hot', 'weight' => 245, 'price' => 345, 'consist' => 'Сливочный сыр, лосось, огурчик, острый соус'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Констанция Корлеоне', 'weight' => 260, 'price' => 370, 'consist' => 'Сливочный сыр, обжаренный лосось, зеленый лучок, тобика,  соус'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Запечённая Лава с креветкой', 'weight' => 290, 'price' => 335, 'consist' => 'Сливочный сыр, креветка, соус'],
            ['product_category_id' => $this->categories['baked-roll'], 'name' => 'Запечённая Лава', 'weight' => 290, 'price' => 335, 'consist' => 'Сливочный сыр, лосось, соус'],
            // Tempura roll
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Эби Маки Темпура', 'weight' => 270, 'price' => 345, 'consist' => 'Сливочный сыр, тигровая креветка, болгарский перец'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Унаги Темпура', 'weight' => 275, 'price' => 345, 'consist' => 'Сливочный сыр, копченый угорь, огурчик'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Темпурная Лава с креветкой', 'weight' => 330, 'price' => 365, 'consist' => 'Сливочный сыр, креветка, соус'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Темпурная Лава', 'weight' => 330, 'price' => 365, 'consist' => 'Сливочный сыр, лосось, соус'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Сяке Терияки Темпура', 'weight' => 270, 'price' => 345, 'consist' => 'Сливочный сыр, обжареный лосось, зеленый лучок, соус Терияки'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Сяке Спайси Темпура', 'weight' => 280, 'price' => 345, 'consist' => 'Сливочный сыр, лосось, томат, соус спайси'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Оливьешка', 'weight' => 330, 'price' => 365, 'consist' => 'Такуан, копченая курочка, огурец, помидор, майонез, васаби'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Гейша Темпура', 'weight' => 270, 'price' => 345, 'consist' => 'Сливочный сыр, лосось, огурец'],
            ['product_category_id' => $this->categories['tempura-roll'], 'name' => 'Восходящее солнце Темпура', 'weight' => 275, 'price' => 350, 'consist' => 'Сливочный сыр, икра летучей рыбы, огурец,  тигровая креветка'],
            // Vegan roll
            ['product_category_id' => $this->categories['vegan-roll'], 'name' => 'Капа Маки', 'weight' => 195, 'price' => 135, 'consist' => 'Огурец'],
            ['product_category_id' => $this->categories['vegan-roll'], 'name' => 'Вегетарианский ролл', 'weight' => 205, 'price' => 230, 'consist' => 'Томат, огурец,  зеленый лук, болгарский перец'],
            // Mexico
            ['product_category_id' => $this->categories['mexico'], 'name' => 'Кесадилья сливочный цыпленок', 'weight' => 240, 'price' => 300, 'consist' => 'Сырная тортилья, сыр, куриное филе, чеснок, лук, соус'],
            ['product_category_id' => $this->categories['mexico'], 'name' => 'Кесадилья с курицей', 'weight' => 250, 'price' => 300, 'consist' => 'Сырная тортилья, сыр, куриное филе, томаты, болгарский перец, соус'],
            ['product_category_id' => $this->categories['mexico'], 'name' => 'Кесадилья с грибами', 'weight' => 250, 'price' => 300, 'consist' => 'Сырная тортилья, шампиньоны, куриное филе, сыр, лук, сливочный соус, зелень'],
            ['product_category_id' => $this->categories['mexico'], 'name' => 'Кесадилья Hot', 'weight' => 250, 'price' => 300, 'consist' => 'Сырная тортилья, сыр, куриное филе, томаты, болгарский перец, острый соус'],
            // Wok
            ['product_category_id' => $this->categories['wok'], 'name' => 'Сливочный вок с курицей', 'weight' => 350, 'price' => 345, 'consist' => 'Лапша удон, куриное филе, шампиньоны, лук, сливочный соус, зелень, чеснок'],
            ['product_category_id' => $this->categories['wok'], 'name' => 'Сливочный вок с креветками', 'weight' => 350, 'price' => 360, 'consist' => 'Лапша удон, креветки, шампиньоны, лук, сливочный соус, чеснок, зелень'],
            ['product_category_id' => $this->categories['wok'], 'name' => 'Рис с курицей терияки', 'weight' => 400, 'price' => 345, 'consist' => 'Рис, куринное филе, болгарский перец, лук, морковь, зеленый лук, соус Терияки'],
            ['product_category_id' => $this->categories['wok'], 'name' => 'Рис с креветками', 'weight' => 400, 'price' => 365, 'consist' => 'Рис, креветки, болгарский перец, лук, морковь, устричный соус, зелень, кунжут'],
            ['product_category_id' => $this->categories['wok'], 'name' => 'Вок с морепродуктами', 'weight' => 385, 'price' => 365, 'consist' => 'Лапша удон, морской коктейль,  болгарский перец, лук, морковь, устричный соус, зелень, кунжут'],
            ['product_category_id' => $this->categories['wok'], 'name' => 'Вок с курицей', 'weight' => 385, 'price' => 345, 'consist' => 'Лапша удон, куриное филе, болгарский перец, лук, морковь, соус Терияки, зелень, кунжут'],
            ['product_category_id' => $this->categories['wok'], 'name' => 'Вок с креветками', 'weight' => 385, 'price' => 365, 'consist' => 'Лапша удон, креветки, болгарский перец, лук, морковь, соус устричный, зелень, кунжут'],
            // Soup
            ['product_category_id' => $this->categories['soup'], 'name' => 'Том Ям с сёмгой', 'weight' => 440, 'price' => 370, 'consist' => 'Тайский суп на кокосовом молоке с сёмгой, помидорами, шампиньонами, красным луком'],
            ['product_category_id' => $this->categories['soup'], 'name' => 'Том Ям с курочкой', 'weight' => 440, 'price' => 360, 'consist' => 'Тайский суп на кокосовом молоке с курочкой, помидорами, шампиньонами, красным луком'],
            ['product_category_id' => $this->categories['soup'], 'name' => 'Том Ям с креветкой', 'weight' => 440, 'price' => 370, 'consist' => 'айский суп на кокосовом молоке с креветками, помидорами, шампиньонами, красным луком'],
            // Snack
            ['product_category_id' => $this->categories['snack'], 'name' => 'Хрустящая креветка', 'weight' => 180, 'price' => 530, 'consist' => 'Тигровые креветки, спринг тесто, соус Айсберг, Кисло-сладкий соус'],
            ['product_category_id' => $this->categories['snack'], 'name' => 'Картошка Фри', 'weight' => 160, 'price' => 180, 'consist' => 'Картошка фри, кетчуп/ сырный соус'],
            ['product_category_id' => $this->categories['snack'], 'name' => 'Картофельные дольки', 'weight' => 160, 'price' => 180, 'consist' => 'Картофельные дольки, соус'],
            // Kit
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор Филадельфия Love', 'weight' => 1020, 'price' => 1650, 'consist' => 'Филадельфия с зеленым лучком, Филадельфия Лайт с авокадо, Запечённая Филадельфия Лайт, сливочный лосось'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор Сакура', 'weight' => 1360, 'price' => 1700, 'consist' => 'Лава с огурчиком, Нежный, Ева, Калифорния с лососем, Татцуми'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор Новогодний', 'weight' => 2200, 'price' => 2450, 'consist' => 'Острый лосось, Фудзияма, Лава, Нежный, Чикен Лайт, Лава с крабом темпура, Эби Грин Мак, Эби Черри Hot'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор Для Двоих', 'weight' => 1040, 'price' => 920, 'consist' => 'Легкий и вкусный  набор для двоих: Лава с огурчиком, Фреш Mix, Острый краб, Чикен Hot,'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор Джон Уик', 'weight' => 1490, 'price' => 1800, 'consist' => 'Тацуми, Эби Грин Маки, Оливьешка, Калифорния с рыбкой, Авокадо Темпура'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор QUEEN', 'weight' => 2730, 'price' => 3450, 'consist' => 'Великолепный набор для особенных: Филадельфия с зеленым луком, Бонито Маки, Филадельфия Лайт, Намиби Эби Маки, Сегун, Унаги Филадельфия, Икура, Гейша, Лава с креветкой, Сливочный лосось, Лава'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор FAMILY', 'weight' => 1650, 'price' => 2400, 'consist' => 'Вито, Майкл, Санни, Констанция, Мэри, Аполлония, вся семья  Корлеоне'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Набор AMORE', 'weight' => 1010, 'price' => 1370, 'consist' => 'В набор AMORE входит: филадельфия лайт, восходящее солнце, лава с креветкой, калифорния с креветкой'],
            ['product_category_id' => $this->categories['kit'], 'name' => 'Лава mix', 'weight' => 1080, 'price' => 1200, 'consist' => 'Лава, Лава с креветкой, Лава со снежным крабом, Лава с лососем терияки'],
            // Drink
            ['product_category_id' => $this->categories['drink'], 'name' => 'Name of Product', 'weight' => 200, 'price' => 250, 'consist' => 'Consist of product'],
            // Salad
            ['product_category_id' => $this->categories['salad'], 'name' => 'Салат Чукка', 'weight' => 150, 'price' => 260, 'consist' => 'Маринованные водоросли чукка, соус'],
        ]);

    }
}
