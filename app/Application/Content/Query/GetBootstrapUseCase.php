<?php

namespace App\Application\Content\Query;

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
use App\Domain\Content\ValueObject\KitchenAddress;

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
        $socials = $company->socials()->toArray();
        $socials['whatsapp'] = self::formatOptionalPhone($socials['whatsapp']);

        return [
            'id' => $company->id(),
            'name' => $company->name(),
            'description' => $company->description(),
            'phone' => self::formatOptionalPhone($company->phone()),
            'email' => $company->email(),
            'socials' => $socials,
            'schedule' => $company->schedule(),
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
            'inn' => $legal->inn(),
            'ogrn' => $legal->ogrn(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDocument(CompanyDocument $document): array
    {
        return [
            'id' => $document->id(),
            'slug' => $document->slug(),
            'name' => $document->name(),
            'content' => $document->content(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDelivery(DeliveryConfiguration $config): array
    {
        $address = $config->kitchenAddress();

        $zones = array_map(
            static fn ($zone): array => $zone->toArray(),
            $config->zones(),
        );

        return [
            'settings' => [
                'min_order_amount_kopecks' => $config->minOrderAmountKopecks(),
                'delivery_fee_kopecks' => $config->minDeliveryFeeKopecks(),
                'outside_zone_delivery_fee_kopecks' => null,
                'average_delivery_time_minutes' => $config->averageDeliveryTimeMinutes(),
            ],
            'zone' => [
                'kitchen_address' => $this->mapKitchenAddress($address),
                'kitchen_latitude' => $config->kitchenLatitude(),
                'kitchen_longitude' => $config->kitchenLongitude(),
                'delivery_zone_geojson' => $config->deliveryZoneGeoJson(),
                'delivery_zones' => $zones,
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
