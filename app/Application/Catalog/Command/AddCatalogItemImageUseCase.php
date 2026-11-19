<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class AddCatalogItemImageUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
    ) {}

    /**
     * @return array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}
     */
    public function execute(int $itemId, UploadedFile $file, ?float $focusX = null, ?float $focusY = null): array
    {
        if ($this->items->findProductById($itemId) === null && $this->items->findSetById($itemId) === null) {
            // Item may be empty set; validate via addItemImage requireItem.
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $basename = Str::uuid()->toString().'.'.$extension;
        $path = 'catalog/items/'.$itemId.'/'.$basename;

        Storage::disk('public')->put($path, $file->getContent());

        return $this->items->addItemImage($itemId, $path, $focusX, $focusY);
    }
}
