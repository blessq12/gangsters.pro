<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\Contracts\ProductImageStoragePort;
use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Catalog\Support\CatalogEventPublisher;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Entity\ProductImage;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\VO\ImageVariant;
use App\Infrastructure\Product\Service\ProductImageVariantService;
use Illuminate\Http\UploadedFile;

final class UploadProductImageUseCase
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductImageStoragePort $storage,
        private readonly ProductImageVariantService $imageVariants,
        private readonly AdminProductPresenter $presenter,
        private readonly CatalogEventPublisher $events,
    ) {
    }

    public function execute(int $productId, UploadedFile $file): array
    {
        $product = $this->products->findById($productId);
        if ($product === null) {
            throw new ApiException('Product not found.', 404);
        }

        $stored = $this->storage->storeThumb($file, $productId);
        $variants = $this->imageVariants->generateVariants($stored['path']);
        if ($variants === null) {
            $variants = [
                'thumb' => $stored,
                'medium' => $stored,
                'large' => $stored,
            ];
        }

        $imageVariants = [];
        foreach ($variants as $size => $variant) {
            $imageVariants[] = new ImageVariant(
                size: $size,
                path: $variant['path'],
                width: (int) $variant['width'],
                height: (int) $variant['height'],
            );
        }

        $images = $product->images();
        $images[] = ProductImage::create($imageVariants, count($images));
        $product->setImages($images);
        $this->products->save($product);
        $this->events->productUpdated($product);

        return $this->presenter->presentDetail($product);
    }
}
