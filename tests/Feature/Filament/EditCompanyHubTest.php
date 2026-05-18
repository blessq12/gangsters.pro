<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Infrastructure\SystemContent\Model\SYS_CompanyLegal;
use App\Infrastructure\SystemContent\Model\SYS_Document;
use App\Models\User;
use App\Support\SystemContent\DocumentKeyLabels;
use Livewire\Livewire;
use Tests\TestCase;

final class EditCompanyHubTest extends TestCase
{
    private const VALID_POLYGON = [
        'type' => 'Polygon',
        'coordinates' => [
            [
                [84.95, 56.48],
                [85.05, 56.48],
                [85.05, 56.52],
                [84.95, 56.52],
                [84.95, 56.48],
            ],
        ],
    ];

    /** @var array<string, mixed>|null */
    private ?array $originalGeojson = null;

    private ?string $originalLegalPhone = null;

    private ?string $originalDocTitle = null;

    private ?int $companyId = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->databaseTableExists('companies') || ! $this->databaseTableExists('users')) {
            $this->markTestSkipped('Нет таблиц companies/users для Filament-теста.');
        }
    }

    protected function tearDown(): void
    {
        if ($this->companyId !== null) {
            SYS_Company::query()->whereKey($this->companyId)->update([
                'delivery_zone_geojson' => $this->originalGeojson,
            ]);
        }

        if ($this->companyId !== null && $this->originalLegalPhone !== null) {
            SYS_CompanyLegal::query()
                ->where('company_id', $this->companyId)
                ->update(['legal_phone' => $this->originalLegalPhone]);
        }

        if ($this->originalDocTitle !== null) {
            SYS_Document::query()
                ->where('key', DocumentKeyLabels::PRIVACY_POLICY)
                ->update(['title' => $this->originalDocTitle]);
        }

        parent::tearDown();
    }

    public function test_edit_company_hub_saves_zone_legal_and_documents(): void
    {
        $company = SYS_Company::query()->first();
        if ($company === null) {
            $this->markTestSkipped('Нет записи companies для проверки.');
        }

        $user = User::query()->first();
        if ($user === null) {
            $this->markTestSkipped('Нет пользователя admin для Filament.');
        }

        $this->companyId = (int) $company->id;
        $this->originalGeojson = $company->delivery_zone_geojson;

        $legal = $company->legal()->firstOrCreate(
            ['company_id' => $company->id],
            [
                'legal_form' => 'ООО',
                'legal_email' => 'legal@example.com',
                'owner' => 'Owner',
                'inn' => '1234567890',
                'kpp' => '123456789',
                'ogrn' => '1234567890123',
                'okpo' => '12345678',
                'registration_address' => 'Адрес',
            ]
        );
        $this->originalLegalPhone = $legal->legal_phone;

        $doc = SYS_Document::query()->firstOrCreate(
            ['key' => DocumentKeyLabels::PRIVACY_POLICY],
            ['title' => DocumentKeyLabels::defaultTitle(DocumentKeyLabels::PRIVACY_POLICY), 'content' => '', 'is_active' => true]
        );
        $this->originalDocTitle = $doc->title;

        $base = $company->only([
            'name', 'description', 'country', 'state', 'city', 'street', 'house',
            'phone', 'email_address', 'work_schedule',
        ]);

        Livewire::actingAs($user)
            ->test(EditCompany::class, ['record' => $company->getKey()])
            ->fillForm(array_merge($base, [
                'delivery_zone_geojson' => self::VALID_POLYGON,
                'kitchen_latitude' => 56.5,
                'kitchen_longitude' => 85.0,
                'legal' => array_merge($legal->toArray(), [
                    'legal_phone' => '+7 (900) 111-22-33',
                ]),
                'documents' => [
                    DocumentKeyLabels::PRIVACY_POLICY => [
                        'title' => 'Тестовая политика',
                        'content' => '<p>Текст</p>',
                        'is_active' => true,
                    ],
                    DocumentKeyLabels::TERMS_OF_USE => [
                        'title' => DocumentKeyLabels::defaultTitle(DocumentKeyLabels::TERMS_OF_USE),
                        'content' => '',
                        'is_active' => true,
                    ],
                    DocumentKeyLabels::USER_AGREEMENT => [
                        'title' => DocumentKeyLabels::defaultTitle(DocumentKeyLabels::USER_AGREEMENT),
                        'content' => '',
                        'is_active' => true,
                    ],
                ],
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $company->refresh();
        $legal->refresh();
        $doc->refresh();

        $this->assertIsArray($company->delivery_zone_geojson);
        $this->assertSame('Polygon', $company->delivery_zone_geojson['type'] ?? null);
        $this->assertSame('+7 (900) 111-22-33', $legal->legal_phone);
        $this->assertSame('Тестовая политика', $doc->title);
        $this->assertStringContainsString('Текст', (string) $doc->content);
    }

    private function databaseTableExists(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
