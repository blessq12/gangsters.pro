<?php

namespace App\Application\Content\useCases;

use App\Application\Content\Presenter\MarketingContentPresenter;
use App\Shared\ValueObject\PhoneNumber;
use App\Domain\Content\Entity\Company;
use App\Domain\Content\Entity\CompanyDocument;
use App\Domain\Content\Entity\CompanyLegalInfo;
use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\BannerRepository;
use App\Domain\Content\Repository\CompanyDocumentRepository;
use App\Domain\Content\Repository\CompanyLegalRepository;
use App\Domain\Content\Repository\CompanyRepository;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\Repository\PromotionRepository;
use App\Domain\Content\ValueObject\CompanyContact;
use App\Domain\Content\ValueObject\CompanySchedule;
use App\Domain\Content\ValueObject\KitchenAddress;
use App\Domain\Content\ValueObject\WorkScheduleRow;

/**
 * Single public entry of Content BC: SPA content snapshot.
 */
final class GetBootstrapUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly CompanyLegalRepository $legals,
        private readonly CompanyDocumentRepository $documents,
        private readonly BannerRepository $banners,
        private readonly PromotionRepository $promotions,
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly MarketingContentPresenter $marketingPresenter,
    ) {}

    /**
     * @return array{
     *     version: string,
     *     company: array{main: array<string, mixed>|null, legals: array<string, mixed>|null, documents: list<array<string, mixed>>},
     *     marketing: array{banners: list<array<string, mixed>>, promotions: list<array<string, mixed>>},
     *     delivery: array<string, mixed>|null
     * }
     */
    public function execute(): array
    {
        $company = $this->companies->findPublic();
        $legal = $this->legals->findPublic();
        $config = $this->delivery->findPublic();

        return [
            'version' => gmdate('c'),
            'company' => [
                'main' => $company instanceof Company ? $this->mapCompany($company) : null,
                'legals' => $legal instanceof CompanyLegalInfo ? $this->mapLegal($legal) : null,
                'documents' => array_map(
                    fn (CompanyDocument $document): array => $this->mapDocument($document),
                    $this->documents->findAllOrdered(),
                ),
            ],
            'marketing' => $this->marketingPresenter->present(
                $this->banners->findActiveOrdered(),
                $this->promotions->findActiveOrdered(),
            ),
            'delivery' => $config instanceof DeliveryConfiguration
                ? $this->mapDelivery($config)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCompany(Company $company): array
    {
        return [
            'id' => $company->id(),
            'name' => $company->name(),
            'brand_name' => $company->brandName(),
            'description' => $company->description(),
            'tagline' => $company->tagline(),
            ...$this->mapContact($company->contact()),
            ...$this->mapSchedule($company->schedule()),
            'logo' => $company->logo(),
            'telegram' => $company->telegram(),
            'site_url' => $company->siteUrl(),
            'vk' => $company->vk(),
            'inst' => $company->inst(),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function mapContact(CompanyContact $contact): array
    {
        return [
            'phone' => self::formatOptionalPhone($contact->phone()),
            'phone_additional' => self::formatOptionalPhone($contact->phoneAdditional()),
            'support_phone' => self::formatOptionalPhone($contact->supportPhone()),
            'whatsapp_phone' => self::formatOptionalPhone($contact->whatsappPhone()),
            'email_address' => $contact->emailAddress(),
            'public_email' => $contact->publicEmail(),
        ];
    }

    /**
     * @return array{work_hours: string|null, work_schedule: list<array<string, mixed>>}
     */
    private function mapSchedule(CompanySchedule $schedule): array
    {
        return [
            'work_hours' => $schedule->workHours(),
            'work_schedule' => array_map(
                fn (WorkScheduleRow $row): array => [
                    'day' => $row->day(),
                    'work' => $row->work(),
                    'is_day_off' => $row->isDayOff(),
                ],
                $schedule->workSchedule(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapLegal(CompanyLegalInfo $legal): array
    {
        return [
            'id' => $legal->id(),
            'company_id' => $legal->companyId(),
            'full_name' => $legal->fullName(),
            'short_name' => $legal->shortName(),
            'legal_form' => $legal->legalForm(),
            'legal_email' => $legal->legalEmail(),
            'contracts_email' => $legal->contractsEmail(),
            'legal_phone' => self::formatOptionalPhone($legal->legalPhone()),
            'owner' => $legal->owner(),
            'responsible_person' => $legal->responsiblePerson(),
            'responsible_position' => $legal->responsiblePosition(),
            'inn' => $legal->inn(),
            'ogrn' => $legal->ogrn(),
            'ogrnip' => $legal->ogrnip(),
            'okpo' => $legal->okpo(),
            'kpp' => $legal->kpp(),
            'tax_system' => $legal->taxSystem(),
            'is_vat_payer' => $legal->isVatPayer(),
            'vat_rate_default' => $legal->vatRateDefault(),
            'registration_address' => $legal->registrationAddress(),
            'actual_address' => $legal->actualAddress(),
            'postal_address' => $legal->postalAddress(),
            'bank_name' => $legal->bankName(),
            'bik' => $legal->bik(),
            'checking_account' => $legal->checkingAccount(),
            'correspondent_account' => $legal->correspondentAccount(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDocument(CompanyDocument $document): array
    {
        return [
            'id' => $document->id(),
            'key' => $document->key(),
            'title' => $document->title(),
            'content' => $document->content(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDelivery(DeliveryConfiguration $config): array
    {
        $address = $config->kitchenAddress();

        return [
            'settings' => [
                'min_order_amount_kopecks' => $config->minOrderAmountKopecks(),
                'delivery_fee_kopecks' => $config->deliveryFeeKopecks(),
                'outside_zone_delivery_fee_kopecks' => $config->outsideZoneDeliveryFeeKopecks(),
                'average_delivery_time_minutes' => $config->averageDeliveryTimeMinutes(),
            ],
            'zone' => [
                'kitchen_address' => $this->mapKitchenAddress($address),
                'kitchen_latitude' => $config->kitchenLatitude(),
                'kitchen_longitude' => $config->kitchenLongitude(),
                'delivery_zone_geojson' => $config->deliveryZoneGeoJson(),
            ],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function mapKitchenAddress(KitchenAddress $address): array
    {
        return [
            'city' => $address->city(),
            'street' => $address->street(),
            'house' => $address->house(),
            'comment' => $address->comment(),
            'search_line' => $address->searchLine(),
        ];
    }

    private static function formatOptionalPhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        return PhoneNumber::tryFormatFromRaw($phone) ?? trim($phone);
    }
}
