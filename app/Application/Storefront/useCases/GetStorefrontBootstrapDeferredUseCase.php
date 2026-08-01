<?php

namespace App\Application\Storefront\useCases;

use App\Application\Catalog\useCases\GetCatalogUseCase;

/**
 * Deferred bootstrap: полный каталог после critical.
 */
final class GetStorefrontBootstrapDeferredUseCase
{
    public function __construct(
        private readonly GetCatalogUseCase $catalog,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'catalog' => $this->catalog->execute(),
        ];
    }
}
