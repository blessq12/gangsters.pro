<?php

namespace App\Infrastructure\Storage\Migration;

final class MigrationReport
{
    /** @var array<string, int> */
    private array $read = [];

    /** @var array<string, int> */
    private array $written = [];

    /** @var list<string> */
    private array $skipped = [];

    /** @var list<string> */
    private array $notes = [];

    public function addRead(string $table, int $count): void
    {
        $this->read[$table] = ($this->read[$table] ?? 0) + $count;
    }

    public function addWritten(string $table, int $count): void
    {
        $this->written[$table] = ($this->written[$table] ?? 0) + $count;
    }

    public function skip(string $reason): void
    {
        $this->skipped[] = $reason;
    }

    public function note(string $message): void
    {
        $this->notes[] = $message;
    }

    /**
     * @return array<string, int>
     */
    public function read(): array
    {
        return $this->read;
    }

    /**
     * @return array<string, int>
     */
    public function written(): array
    {
        return $this->written;
    }

    /**
     * @return list<string>
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    /**
     * @return list<string>
     */
    public function notes(): array
    {
        return $this->notes;
    }

    public function merge(self $other): void
    {
        foreach ($other->read as $table => $count) {
            $this->addRead($table, $count);
        }
        foreach ($other->written as $table => $count) {
            $this->addWritten($table, $count);
        }
        foreach ($other->skipped as $reason) {
            $this->skip($reason);
        }
        foreach ($other->notes as $note) {
            $this->note($note);
        }
    }
}
