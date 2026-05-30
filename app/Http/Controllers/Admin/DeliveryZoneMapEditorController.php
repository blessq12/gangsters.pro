<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;

final class DeliveryZoneMapEditorController
{
    public function __invoke(): View
    {
        return view('admin.delivery-zone-map-editor', [
            'apiKey' => (string) config('services.yandex_maps.api_key'),
        ]);
    }
}
