<?php

namespace App\Support\Bootstrap;

use App\Application\Catalog\useCases\GetCatalogUseCase;

/**
 * Deferred SPA bootstrap: full catalog.
 */
final class GetAppBootstrapDeferredUseCase
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
