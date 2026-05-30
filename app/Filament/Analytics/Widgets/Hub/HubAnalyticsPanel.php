<?php

namespace App\Filament\Analytics\Widgets\Hub;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;

abstract class HubAnalyticsPanel extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    protected static bool $isDiscovered = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.analytics.hub-panel';

    protected int|string|array $columnSpan = 'full';
}
