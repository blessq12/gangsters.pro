<?php

namespace App\Filament\Catalog\Concerns;

use App\Filament\Catalog\Resources\CatalogResource;
use Filament\Resources\Pages\EditRecord;

trait CatalogContextBreadcrumbs
{
    abstract protected static function catalogHubTab(): string;

    public function hasResourceBreadcrumbs(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            CatalogResource::getUrl('index') => CatalogResource::getNavigationLabel() ?? 'Каталог',
            $this->catalogHubUrl() => static::catalogHubTabLabel(),
        ];

        if ($this instanceof EditRecord && $this->hasRecord()) {
            $breadcrumbs[] = $this->getRecordTitle();

            return $breadcrumbs;
        }

        $breadcrumbs[] = $this->getBreadcrumbLabel();

        return $breadcrumbs;
    }

    protected function catalogHubUrl(): string
    {
        return CatalogResource::getUrl('index', [
            'tab' => static::catalogHubTab(),
        ]);
    }

    protected static function catalogHubTabLabel(): string
    {
        return match (static::catalogHubTab()) {
            'categories' => 'Категории',
            'products' => 'Товары',
            'sets' => 'Наборы',
            'tags' => 'Теги',
            default => 'Каталог',
        };
    }

    protected function getBreadcrumbLabel(): string
    {
        if (filled(static::$title ?? null)) {
            return static::$title;
        }

        return $this->getTitle();
    }
}
