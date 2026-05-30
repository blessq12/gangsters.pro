<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class YandexDeliveryZoneMap extends Field
{
    protected string $view = 'filament.forms.components.yandex-delivery-zone-map';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(true);
    }
}
