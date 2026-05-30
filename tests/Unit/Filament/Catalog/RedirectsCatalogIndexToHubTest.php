<?php

namespace Tests\Unit\Filament\Catalog;

use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Resources\TagResource;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RedirectsCatalogIndexToHubTest extends TestCase
{
    #[Test]
    public function product_resource_index_url_points_to_products_tab(): void
    {
        $url = ProductResource::getIndexUrl();

        $this->assertStringContainsString('/admin/catalog', $url);
        $this->assertStringContainsString('tab=products', $url);
    }

    #[Test]
    public function category_resource_index_url_points_to_categories_tab(): void
    {
        $url = CategoryResource::getIndexUrl();

        $this->assertStringContainsString('/admin/catalog', $url);
        $this->assertStringContainsString('tab=categories', $url);
    }

    #[Test]
    public function tag_resource_index_url_points_to_tags_tab(): void
    {
        $url = TagResource::getIndexUrl();

        $this->assertStringContainsString('/admin/catalog', $url);
        $this->assertStringContainsString('tab=tags', $url);
    }
}
