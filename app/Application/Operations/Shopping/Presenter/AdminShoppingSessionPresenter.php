<?php

namespace App\Application\Operations\Shopping\Presenter;

use App\Application\Operations\Client\Contracts\AdminClientReadRepository;
use App\Application\Operations\Shopping\Contracts\AdminShoppingProductReadRepository;
use App\Domain\Shopping\Entities\CartLine;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Support\Shopping\AdminCheckoutDraftFormatter;
use DateTimeImmutable;

final class AdminShoppingSessionPresenter
{
    public function __construct(
        private readonly AdminShoppingProductReadRepository $products,
        private readonly AdminClientReadRepository $clients,
    ) {
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function presentListItem(array $row): array
    {
        return [
            'id' => $row['id'],
            'public_id' => $row['public_id'],
            'client_id' => $row['client_id'],
            'client_label' => $row['client_label'],
            'client_badge_color' => $row['client_id'] !== null ? 'success' : 'gray',
            'cart_lines_count' => $row['cart_lines_count'],
            'favorites_count' => $row['favorites_count'],
            'updated_at' => $row['updated_at'],
            'expires_at' => $row['expires_at'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function present(ShoppingSession $session): array
    {
        $productIds = array_unique(array_merge(
            array_map(static fn (CartLine $line): int => $line->productId, $session->getCartLines()),
            $session->getFavoriteProductIds(),
        ));

        $productsById = $this->products->findSummariesByIds($productIds);
        $client = $this->presentClient($session);

        $cartLines = [];
        $totalQuantity = 0;
        foreach ($session->getCartLines() as $line) {
            $product = $productsById[$line->productId] ?? null;
            $unitKopecks = $product['price_kopecks'] ?? null;
            $lineTotalKopecks = $unitKopecks !== null ? $unitKopecks * $line->quantity : null;
            $totalQuantity += $line->quantity;

            $cartLines[] = [
                'product_id' => $line->productId,
                'product_name' => $this->productName($line->productId, $product),
                'quantity' => $line->quantity,
                'unit_price_label' => $unitKopecks !== null
                    ? AdminCheckoutDraftFormatter::formatRubles($unitKopecks)
                    : null,
                'line_total_label' => $lineTotalKopecks !== null
                    ? AdminCheckoutDraftFormatter::formatRubles($lineTotalKopecks)
                    : null,
                'product_status' => $product['status'] ?? null,
            ];
        }

        $favoriteItems = [];
        foreach ($session->getFavoriteProductIds() as $productId) {
            $product = $productsById[$productId] ?? null;
            $favoriteItems[] = [
                'product_id' => $productId,
                'product_name' => $this->productName($productId, $product),
            ];
        }

        $checkoutDraft = $session->getCheckoutDraft();
        $checkoutSections = AdminCheckoutDraftFormatter::sections($checkoutDraft);

        return [
            'session' => [
                'id' => $session->getId(),
                'public_id' => $session->getPublicId(),
                'expires_at' => $this->formatDateTime($session->getExpiresAt()),
                'created_at' => $this->formatDateTime($session->getCreatedAt()),
                'updated_at' => $this->formatDateTime($session->getUpdatedAt()),
            ],
            'client' => $client,
            'cart' => [
                'is_empty' => $cartLines === [],
                'lines_count' => count($cartLines),
                'total_quantity' => $totalQuantity,
                'lines' => $cartLines,
            ],
            'favorites' => [
                'count' => count($favoriteItems),
                'items' => $favoriteItems,
            ],
            'checkout' => [
                'has_draft' => $checkoutDraft !== null && $checkoutDraft !== [],
                'sections' => $checkoutSections,
            ],
        ];
    }

    /**
     * @return array{
     *     type: string,
     *     type_label: string,
     *     id: int|null,
     *     label: string,
     *     phone: string|null,
     *     email: string|null,
     *     badge_color: string
     * }
     */
    private function presentClient(ShoppingSession $session): array
    {
        $clientId = $session->getClientId();
        if ($clientId === null) {
            return [
                'type' => 'guest',
                'type_label' => 'Гость',
                'id' => null,
                'label' => 'Гость',
                'phone' => null,
                'email' => null,
                'badge_color' => 'gray',
            ];
        }

        $client = $this->clients->findProfileSummaryById($clientId);
        if ($client === null) {
            return [
                'type' => 'client',
                'type_label' => 'Клиент',
                'id' => $clientId,
                'label' => 'Клиент #'.$clientId,
                'phone' => null,
                'email' => null,
                'badge_color' => 'warning',
            ];
        }

        $name = trim((string) ($client['name'] ?? ''));

        return [
            'type' => 'client',
            'type_label' => 'Клиент',
            'id' => $clientId,
            'label' => $name !== '' ? $name : 'Клиент #'.$clientId,
            'phone' => filled($client['phone'] ?? null) ? (string) $client['phone'] : null,
            'email' => filled($client['email'] ?? null) ? (string) $client['email'] : null,
            'badge_color' => 'success',
        ];
    }

    /**
     * @param  array{id: int, name: string, price_kopecks: int|null, status: string|null}|null  $product
     */
    private function productName(int $productId, ?array $product): string
    {
        if ($product === null) {
            return 'Товар #'.$productId;
        }

        $name = trim($product['name']);

        return $name !== '' ? $name : 'Товар #'.$productId;
    }

    private function formatDateTime(DateTimeImmutable $dateTime): string
    {
        return $dateTime->format('d.m.Y H:i');
    }
}
