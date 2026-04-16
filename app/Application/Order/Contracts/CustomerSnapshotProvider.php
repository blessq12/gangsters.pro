<?php

namespace App\Application\Order\Contracts;

use App\Domain\Order\ValueObjects\CustomerSnapshot;

interface CustomerSnapshotProvider
{
    public function forAuthenticatedClient(int $clientId): CustomerSnapshot;

    public function forGuestContact(string $name, string $phone, ?string $email): CustomerSnapshot;

    public function forExternalContact(string $name, string $phone): CustomerSnapshot;
}
