<?php

namespace App\Filament\Support;

use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartLine;
use App\Infrastructure\Shopping\Model\SHP_ShoppingFavorite;
use App\Infrastructure\Shopping\Model\SHP_ShoppingSession;
use App\Support\Shopping\AdminCheckoutDraftFormatter;

final class AdminActiveCartSnapshotBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $sessionId): array
    {
        $session = SHP_ShoppingSession::query()
            ->with(['cartLines', 'favorites', 'checkoutDraft'])
            ->find($sessionId);

        if ($session === null) {
            throw new ApiException('Shopping-сессия не найдена.', 404);
        }

        $productIds = collect($session->cartLines)
            ->pluck('product_id')
            ->merge($session->favorites->pluck('product_id'))
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $productsById = $this->loadProductSummaries($productIds);
        $client = $this->presentClient($session->client_id);

        $cartLines = [];
        $totalQuantity = 0;
        foreach ($session->cartLines as $line) {
            /** @var SHP_ShoppingCartLine $line */
            $productId = (int) $line->product_id;
            $product = $productsById[$productId] ?? null;
            $unitKopecks = $product['price_kopecks'] ?? null;
            $quantity = (int) $line->quantity;
            $lineTotalKopecks = $unitKopecks !== null ? $unitKopecks * $quantity : null;
            $totalQuantity += $quantity;

            $cartLines[] = [
                'product_id' => $productId,
                'product_name' => $this->productName($productId, $product),
                'quantity' => $quantity,
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
        foreach ($session->favorites as $favorite) {
            /** @var SHP_ShoppingFavorite $favorite */
            $productId = (int) $favorite->product_id;
            $product = $productsById[$productId] ?? null;
            $favoriteItems[] = [
                'product_id' => $productId,
                'product_name' => $this->productName($productId, $product),
            ];
        }

        $checkoutDraft = $session->checkoutDraft?->payload;
        $checkoutSections = AdminCheckoutDraftFormatter::sections(
            is_array($checkoutDraft) ? $checkoutDraft : null,
        );

        return [
            'session' => [
                'id' => (int) $session->id,
                'public_id' => (string) $session->public_id,
                'expires_at' => $session->expires_at?->format('d.m.Y H:i'),
                'created_at' => $session->created_at?->format('d.m.Y H:i'),
                'updated_at' => $session->updated_at?->format('d.m.Y H:i'),
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
                'has_draft' => is_array($checkoutDraft) && $checkoutDraft !== [],
                'sections' => $checkoutSections,
            ],
        ];
    }

    /**
     * @param  int[]  $ids
     * @return array<int, array{id: int, name: string, price_kopecks: int|null, status: string|null}>
     */
    private function loadProductSummaries(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $summaries = [];
        foreach (PRD_Product::query()->whereIn('id', $ids)->get(['id', 'name', 'price', 'status']) as $model) {
            $summaries[(int) $model->id] = [
                'id' => (int) $model->id,
                'name' => trim((string) ($model->name ?? '')),
                'price_kopecks' => $model->price !== null ? (int) $model->price : null,
                'status' => $model->status !== null ? (string) $model->status : null,
            ];
        }

        return $summaries;
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
    private function presentClient(?int $clientId): array
    {
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

        $client = UR_Client::query()
            ->select(['id', 'name', 'phone', 'email'])
            ->find($clientId);

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

        $name = trim((string) ($client->name ?? ''));

        return [
            'type' => 'client',
            'type_label' => 'Клиент',
            'id' => $clientId,
            'label' => $name !== '' ? $name : 'Клиент #'.$clientId,
            'phone' => filled($client->phone) ? (string) $client->phone : null,
            'email' => filled($client->email) ? (string) $client->email : null,
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
}
