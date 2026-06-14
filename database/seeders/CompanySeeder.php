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

        CMP_Company::query()->updateOrCreate(
            ['id' => $companyId],
            [
                'name' => 'Gangsters',
                'brand_name' => 'Gangsters',
                'description' => 'Ресторан в Томске',
                'tagline' => 'Еда с характером',
                'phone' => '73822555555',
                'phone_additional' => '73822555556',
                'support_phone' => '73822555557',
                'whatsapp_phone' => '79001234567',
                'email_address' => 'info@gangsters.local',
                'public_email' => 'hello@gangsters.local',
                'work_hours' => '10:00–22:00',
                'work_schedule' => [
                    ['day' => 'mon', 'work' => '10:00–22:00', 'is_day_off' => false],
                    ['day' => 'tue', 'work' => '10:00–22:00', 'is_day_off' => false],
                    ['day' => 'wed', 'work' => '10:00–22:00', 'is_day_off' => false],
                    ['day' => 'thu', 'work' => '10:00–22:00', 'is_day_off' => false],
                    ['day' => 'fri', 'work' => '10:00–23:00', 'is_day_off' => false],
                    ['day' => 'sat', 'work' => '11:00–23:00', 'is_day_off' => false],
                    ['day' => 'sun', 'work' => '', 'is_day_off' => true],
                ],
                'logo' => '/images/logo.png',
                'telegram' => 'https://t.me/gangsters',
                'site_url' => 'https://gangsters.local',
                'vk' => 'https://vk.com/gangsters',
                'inst' => 'https://instagram.com/gangsters',
            ],
        );

        CMP_CompanyLegal::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'full_name' => 'Общество с ограниченной ответственностью «Гангстерс»',
                'short_name' => 'ООО «Гангстерс»',
                'legal_form' => 'ООО',
                'legal_email' => 'legal@gangsters.local',
                'contracts_email' => 'contracts@gangsters.local',
                'legal_phone' => '73822555558',
                'owner' => 'Иванов Иван Иванович',
                'responsible_person' => 'Петров Пётр Петрович',
                'responsible_position' => 'Генеральный директор',
                'inn' => '7012345678',
                'ogrn' => '1027001234567',
                'ogrnip' => null,
                'okpo' => '12345678',
                'kpp' => '701201001',
                'tax_system' => 'УСН',
                'is_vat_payer' => false,
                'vat_rate_default' => 0,
                'registration_address' => '634050, г. Томск, пр. Ленина, д. 10',
                'actual_address' => '634050, г. Томск, пр. Ленина, д. 10',
                'postal_address' => '634050, г. Томск, пр. Ленина, д. 10',
                'bank_name' => 'ПАО «Томскбанк»',
                'bik' => '046902728',
                'checking_account' => '40702810123456789012',
                'correspondent_account' => '30101810123456789012',
            ],
        );

        $documents = [
            [
                'key' => 'privacy_policy',
                'title' => 'Политика конфиденциальности',
                'content' => '<p>Настоящая политика описывает порядок обработки персональных данных пользователей сервиса Gangsters.</p>',
            ],
            [
                'key' => 'terms_of_use',
                'title' => 'Условия использования',
                'content' => '<p>Используя сайт и приложение Gangsters, вы соглашаетесь с настоящими условиями использования сервиса.</p>',
            ],
            [
                'key' => 'user_agreement',
                'title' => 'Пользовательское соглашение',
                'content' => '<p>Пользовательское соглашение регулирует отношения между клиентом и ООО «Гангстерс» при оформлении заказов.</p>',
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
