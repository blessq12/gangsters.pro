<?php

namespace App\Infrastructure\Crm\Migration;

/** Сводка прогона миграции legacy users → CRM_clients. */
final class LegacyClientMigrationReport
{
    /** @var list<string> */
    private array $warnings = [];

    public function __construct(
        private int $clientsRead = 0,
        private int $clientsInserted = 0,
        private int $clientsSkipped = 0,
        private int $tokensRead = 0,
        private int $tokensInserted = 0,
        private int $tokensSkipped = 0,
    ) {}

    public function clientsRead(): int
    {
        return $this->clientsRead;
    }

    public function clientsInserted(): int
    {
        return $this->clientsInserted;
    }

    public function clientsSkipped(): int
    {
        return $this->clientsSkipped;
    }

    public function tokensRead(): int
    {
        return $this->tokensRead;
    }

    public function tokensInserted(): int
    {
        return $this->tokensInserted;
    }

    public function tokensSkipped(): int
    {
        return $this->tokensSkipped;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }

    public function bumpClientsRead(int $n = 1): void
    {
        $this->clientsRead += $n;
    }

    public function bumpClientsInserted(int $n = 1): void
    {
        $this->clientsInserted += $n;
    }

    public function bumpClientsSkipped(int $n = 1): void
    {
        $this->clientsSkipped += $n;
    }

    public function bumpTokensRead(int $n = 1): void
    {
        $this->tokensRead += $n;
    }

    public function bumpTokensInserted(int $n = 1): void
    {
        $this->tokensInserted += $n;
    }

    public function bumpTokensSkipped(int $n = 1): void
    {
        $this->tokensSkipped += $n;
    }

    public function warn(string $message): void
    {
        $this->warnings[] = $message;
    }
}
