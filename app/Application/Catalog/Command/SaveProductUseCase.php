<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SaveProductDto;
use App\Domain\Supporting\Catalog\Entity\TechCard;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\Repository\TechCardRepository;
use App\Domain\Supporting\Catalog\ValueObject\TechCardLine;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SaveProductUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
        private readonly TechCardRepository $techCards,
    ) {}

    /**
     * @return array{id: int}
     */
    public function execute(SaveProductDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название товара.');
        }

        $status = ProductStatus::tryFrom($dto->status);
        if ($status === null) {
            throw new \InvalidArgumentException('Некорректный статус товара.');
        }

        $description = trim((string) $dto->description);
        $sku = $dto->sku !== null ? trim($dto->sku) : null;

        $storedPaths = [];

        try {
            $id = DB::transaction(function () use (
                $dto,
                $name,
                $status,
                $description,
                $sku,
                &$storedPaths,
            ): int {
                $itemId = $this->items->saveProduct(
                    $dto->id,
                    $name,
                    $dto->weight,
                    $status->value,
                    $description !== '' ? $description : null,
                    $sku !== '' ? $sku : null,
                    $dto->ingredients,
                    $dto->nutrition,
                    $dto->isRoll,
                );

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

                $techLines = [];
                foreach ($dto->techCardLines as $row) {
                    $techLines[] = new TechCardLine(
                        ingredientRef: (string) ($row['ingredient_ref'] ?? ''),
                        quantity: (float) ($row['quantity'] ?? 0),
                        unit: (string) ($row['unit'] ?? ''),
                    );
                }
                $this->techCards->save(TechCard::forProduct($itemId, $techLines));

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

        return ['id' => $id];
    }
}
