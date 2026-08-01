<?php

namespace App\Filament\Content\Delivery\Forms\Components;

use Filament\Forms\Components\Field;

class YandexDeliveryZoneMap extends Field
{
    protected string $view = 'filament.forms.components.yandex-delivery-zone-map';

    protected string $kitchenAddressRelativePath = 'kitchen_address';

    protected string $kitchenLatitudeRelativePath = 'kitchen_latitude';

    protected string $kitchenLongitudeRelativePath = 'kitchen_longitude';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(true);
    }

    public function kitchenAddressRelativePath(string $path): static
    {
        $this->kitchenAddressRelativePath = $path;

        return $this;
    }

    public function kitchenLatitudeRelativePath(string $path): static
    {
        $this->kitchenLatitudeRelativePath = $path;

        return $this;
    }

    public function kitchenLongitudeRelativePath(string $path): static
    {
        $this->kitchenLongitudeRelativePath = $path;

        return $this;
    }

    public function resolveSiblingStatePath(string $relativePath): string
    {
        $geometryPath = $this->getStatePath();
        $prefix = str_contains($geometryPath, '.')
            ? substr($geometryPath, 0, (int) strrpos($geometryPath, '.'))
            : '';

        if ($prefix === '') {
            return $relativePath;
        }

        return "{$prefix}.{$relativePath}";
    }

    public function getKitchenAddressStatePath(): string
    {
        return $this->resolveSiblingStatePath($this->kitchenAddressRelativePath);
    }

    public function getKitchenLatitudeStatePath(): string
    {
        return $this->resolveSiblingStatePath($this->kitchenLatitudeRelativePath);
    }

    public function getKitchenLongitudeStatePath(): string
    {
        return $this->resolveSiblingStatePath($this->kitchenLongitudeRelativePath);
    }
}
