<?php

namespace App\Http\Controllers\Admin;

use App\Application\SystemContent\Support\CompanyKitchenAddressFormatter;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use Illuminate\Http\Response;

final class DeliveryZoneMapEditorController
{
    public function show(SYS_Company $company): Response
    {
        $apiKey = config('services.yandex_maps.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            abort(503, 'YANDEX_MAPS_API_KEY не настроен.');
        }

        $content = view('admin.delivery-zone-map-editor', [
            'apiKey' => $apiKey,
            'editorConfig' => [
                'geometry' => $company->delivery_zone_geojson,
                'kitchenLatitude' => $company->kitchen_latitude,
                'kitchenLongitude' => $company->kitchen_longitude,
                'address' => CompanyKitchenAddressFormatter::format($company),
            ],
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
