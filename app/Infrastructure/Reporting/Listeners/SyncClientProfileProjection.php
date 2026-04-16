<?php

namespace App\Infrastructure\Reporting\Listeners;

use App\Domain\Client\Events\ClientAddressAdded;
use App\Domain\Client\Events\ClientAddressDeleted;
use App\Domain\Client\Events\ClientRegistered;
use App\Infrastructure\Reporting\Model\ReportingClientProfile;
use Illuminate\Support\Facades\Schema;

final class SyncClientProfileProjection
{
    private static ?bool $tableExists = null;

    public function handle(ClientRegistered|ClientAddressAdded|ClientAddressDeleted $event): void
    {
        if (! $this->projectionTableExists()) {
            return;
        }

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

    private function projectionTableExists(): bool
    {
        if (self::$tableExists === null) {
            self::$tableExists = Schema::hasTable('reporting_client_profiles');
        }

        return self::$tableExists;
    }
}
