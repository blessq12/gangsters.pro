<?php

namespace App\Application\Operations\Shopping\DTO;

final readonly class AdminActiveCartListFilters
{
    public function __construct(
        public ?int $clientId = null,
        public ?int $sessionId = null,
        public ?string $publicId = null,
        public ?string $orderId = null,
    ) {}

    public function isActive(): bool
    {
        return $this->clientId !== null
            || $this->sessionId !== null
            || filled($this->publicId)
            || filled($this->orderId);
    }

    public static function fromSearch(?string $search): self
    {
        $term = trim((string) $search);
        if ($term === '') {
            return new self;
        }

        if (self::looksLikeUuid($term)) {
            return new self(publicId: $term, orderId: $term);
        }

        if (ctype_digit($term)) {
            $id = (int) $term;

            return new self(sessionId: $id, clientId: $id);
        }

        return new self(publicId: $term);
    }

    private static function looksLikeUuid(string $term): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $term,
        );
    }
}
