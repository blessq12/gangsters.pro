<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Admin\AdminAccess;
use App\Domain\Admin\Enums\AdminHub;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

final class DeliveryZoneMapEditorController
{
    public function __invoke(): View
    {
        $user = auth()->user();

        abort_unless(
            $user instanceof User
                && AdminAccess::canAccessHub($user, AdminHub::Operations)
                && AdminAccess::canMutate($user),
            Response::HTTP_FORBIDDEN,
        );

        return view('admin.delivery-zone-map-editor', [
            'mapsApiKey' => (string) config('services.yandex_maps.api_key'),
            'geocoderApiKey' => (string) config('services.yandex_maps.geocoder_api_key'),
        ]);
    }
}
