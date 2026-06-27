<?php

namespace Database\Seeders;

use App\Domain\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Model\CMP_Company;
use App\Infrastructure\Company\Model\CMP_CompanyDocument;
use App\Infrastructure\Company\Model\CMP_CompanyLegal;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companyId = CompanyRepository::SINGLETON_ID;

        $workHours = '10:00–20:00';
        $workSchedule = collect(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])
            ->map(fn (string $day): array => [
                'day' => $day,
                'work' => $workHours,
                'is_day_off' => false,
            ])
            ->all();

        $address = 'Россия, Томская область, Томск, ул. Говорова, 50';

        CMP_Company::query()->updateOrCreate(
            ['id' => $companyId],
            [
                'name' => 'Гангстерс Суши',
                'brand_name' => "Gangster's Sushi",
                'description' => 'Мы предлагаем свежие и вкусные блюда, приготовленные с любовью и вниманием к деталям. Наслаждайтесь японской кухней, не выходя из дома! Быстрая доставка и отличное качество',
                'tagline' => 'Еда с характером',
                'phone' => '+7 (983) 234-84-84',
                'phone_additional' => '+7 (983) 234-34-38',
                'support_phone' => null,
                'whatsapp_phone' => null,
                'email_address' => 'gangstasushi@mail.ru',
                'public_email' => 'gangstasushi@mail.ru',
                'work_hours' => $workHours,
                'work_schedule' => $workSchedule,
                'logo' => '/images/logo.png',
                'telegram' => null,
                'site_url' => null,
                'vk' => 'https://vk.com/gangsters_sushi',
                'inst' => 'https://www.instagram.com/gangsters_sushi',
            ],
        );

        CMP_CompanyLegal::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'full_name' => "Gangster's Sushi",
                'short_name' => 'Пятчин Никита Романович',
                'legal_form' => 'ИП',
                'legal_email' => 'gangstasushi@mail.ru',
                'contracts_email' => 'gangstasushi@mail.ru',
                'legal_phone' => '+7 (983) 234-84-84',
                'owner' => 'Пятчин Никита Романович',
                'responsible_person' => 'Пятчин Никита Романович',
                'responsible_position' => 'Ответственный',
                'inn' => '701717375759',
                'ogrn' => null,
                'ogrnip' => '325700000011686',
                'okpo' => '2040573992',
                'kpp' => null,
                'tax_system' => 'УСН',
                'is_vat_payer' => false,
                'vat_rate_default' => 0,
                'registration_address' => $address,
                'actual_address' => $address,
                'postal_address' => $address,
                'bank_name' => null,
                'bik' => null,
                'checking_account' => null,
                'correspondent_account' => null,
            ],
        );

        $documents = [
            [
                'key' => 'privacy_policy',
                'title' => 'Политика конфиденциальности',
                'content' => '<p>Настоящая политика описывает порядок обработки персональных данных пользователей сервиса Gangster\'s Sushi.</p>',
            ],
            [
                'key' => 'terms_of_use',
                'title' => 'Условия использования',
                'content' => '<p>Используя сайт и приложение Gangster\'s Sushi, вы соглашаетесь с настоящими условиями использования сервиса.</p>',
            ],
            [
                'key' => 'user_agreement',
                'title' => 'Пользовательское соглашение',
                'content' => '<p>Пользовательское соглашение регулирует отношения между клиентом и ИП Пятчин Никита Романович (Gangster\'s Sushi) при оформлении заказов.</p>',
            ],
        ];

        foreach ($documents as $document) {
            CMP_CompanyDocument::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'key' => $document['key'],
                ],
                [
                    'title' => $document['title'],
                    'content' => $document['content'],
                ],
            );
        }
    }
}
