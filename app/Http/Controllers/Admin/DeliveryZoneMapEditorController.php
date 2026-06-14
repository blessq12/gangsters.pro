<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class DeliveryZoneMapEditorController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.delivery-zone-map-editor', [
            'mapsApiKey' => config('services.yandex_maps.api_key'),
            'geocoderApiKey' => config('services.yandex_maps.geocoder_api_key'),
        ]);
    }
}
