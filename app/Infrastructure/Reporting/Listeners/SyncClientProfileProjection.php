<?php

namespace App\Infrastructure\Reporting\Listeners;

use App\Domain\Client\Events\ClientAddressAdded;
use App\Domain\Client\Events\ClientAddressDeleted;
use App\Domain\Client\Events\ClientRegistered;
use App\Infrastructure\Reporting\Model\ReportingClientProfile;

final class SyncClientProfileProjection
{
    public function handle(ClientRegistered|ClientAddressAdded|ClientAddressDeleted $event): void
    {
        $client = $event->client();
        $clientId = $client->id();

        if ($clientId === null) {
            return;
        }

        ReportingClientProfile::query()->updateOrCreate(
            ['client_id' => $clientId],
            ['addresses_count' => count($client->addresses())],
        );
    }
}
