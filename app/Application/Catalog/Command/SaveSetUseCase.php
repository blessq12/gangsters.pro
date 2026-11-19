<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SaveSetDto;
use App\Domain\Supporting\Catalog\Enum\PriceType;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\Event\CatalogItemPriceChanged;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SaveSetUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return array{id: int}
     */
    public function execute(SaveSetDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название набора.');
        }

        $status = ProductStatus::tryFrom($dto->status);
        if ($status === null) {
            throw new \InvalidArgumentException('Некорректный статус набора.');
        }

        $prices = $this->normalizePrices($dto->prices);
        $sku = $dto->sku !== null ? trim($dto->sku) : null;
        $description = trim((string) $dto->description);

        $before = $dto->id !== null ? $this->items->findSetById($dto->id) : null;
        $priceBefore = $before?->regularPrice()?->amountKopecks();

        $storedPaths = [];

        try {
            $id = DB::transaction(function () use (
                $dto,
                $name,
                $prices,
                $status,
                $sku,
                $description,
                &$storedPaths,
            ): int {
                $itemId = $this->items->saveSet(
                    $dto->id,
                    $name,
                    $sku !== '' ? $sku : null,
                    $prices,
                    $status->value,
                    $description !== '' ? $description : null,
                );

                if ($dto->lines !== null) {
                    $this->items->replaceSetLines($itemId, $dto->lines);
                }

                foreach ($dto->deletedImageIds as $imageId) {
                    $this->items->deleteItemImage($itemId, (int) $imageId);
                }

                foreach ($dto->existingImages as $image) {
                    $imageId = (int) ($image['id'] ?? 0);
                    if ($imageId < 1) {
                        continue;
                    }
                    $this->items->updateItemImage(
                        $itemId,
                        $imageId,
                        isset($image['focus_x']) ? (float) $image['focus_x'] : null,
                        isset($image['focus_y']) ? (float) $image['focus_y'] : null,
                        isset($image['sort_order']) ? (int) $image['sort_order'] : null,
                    );
                }

                foreach ($dto->newImages as $row) {
                    $file = $row['file'] ?? null;
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }
                    $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
                    $basename = Str::uuid()->toString().'.'.$extension;
                    $path = 'catalog/items/'.$itemId.'/'.$basename;
                    Storage::disk('public')->put($path, $file->getContent());
                    $storedPaths[] = $path;

                    $focusX = array_key_exists('focus_x', $row) && $row['focus_x'] !== null
                        ? (float) $row['focus_x']
                        : null;
                    $focusY = array_key_exists('focus_y', $row) && $row['focus_y'] !== null
                        ? (float) $row['focus_y']
                        : null;

                    $created = $this->items->addItemImage($itemId, $path, $focusX, $focusY);
                    if (isset($row['sort_order']) && $row['sort_order'] !== null) {
                        $this->items->updateItemImage(
                            $itemId,
                            (int) $created['id'],
                            $focusX,
                            $focusY,
                            (int) $row['sort_order'],
                        );
                    }
                }

                return $itemId;
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            throw $exception;
        }

        $priceAfter = $this->items->findSetById($id)?->regularPrice()?->amountKopecks();
        if ($before !== null && $priceAfter !== null && $priceAfter !== $priceBefore) {
            event(new CatalogItemPriceChanged(
                catalogItemId: $id,
                name: $name,
                regularPriceKopecks: $priceAfter,
            ));
        }

        return ['id' => $id];
    }

    /**
     * @param  list<array{type?: mixed, amount?: mixed}>  $prices
     * @return list<array{type: string, amount: int}>
     */
    private function normalizePrices(array $prices): array
    {
        $normalized = [];
        foreach ($prices as $row) {
            if (! is_array($row)) {
                continue;
            }
            $type = PriceType::tryFrom((string) ($row['type'] ?? ''));
            if ($type === null || $type === PriceType::Promo) {
                throw new \InvalidArgumentException('Неизвестный тип цены: '.(string) ($row['type'] ?? ''));
            }
            if (! array_key_exists('amount', $row) || ! is_numeric($row['amount'])) {
                throw new \InvalidArgumentException('У цены '.$type->value.' нужна сумма.');
            }
            $amount = (int) $row['amount'];
            if ($amount < 0) {
                throw new \InvalidArgumentException('Цена не может быть отрицательной.');
            }
            if (isset($normalized[$type->value])) {
                throw new \InvalidArgumentException('Тип цены '.$type->value.' указан дважды.');
            }
            $normalized[$type->value] = [
                'type' => $type->value,
                'amount' => $amount,
            ];
        }

        if ($normalized === []) {
            throw new \InvalidArgumentException('Нужна хотя бы одна цена.');
        }
        if (! isset($normalized[PriceType::Regular->value])) {
            throw new \InvalidArgumentException('Обязательна цена типа regular.');
        }

        return array_values($normalized);
    }
}
