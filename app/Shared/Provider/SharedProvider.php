<?php

namespace App\Shared\Provider;

use App\Infrastructure\Shared\Geo\YandexAddressGeocoder;
use App\Shared\Geo\AddressGeocoder;
use Illuminate\Support\ServiceProvider;

final class SharedProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AddressGeocoder::class, YandexAddressGeocoder::class);
    }
}
