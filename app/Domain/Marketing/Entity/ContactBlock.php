<?php

namespace App\Domain\Marketing\Entity;

/**
 * Витринный контактный блок (принадлежит Marketing, может дублировать телефон/email компании для bootstrap).
 */
final class ContactBlock
{
    /**
     * @param  array<string, mixed>  $extra  Дополнительные контакты витрины
     */
    public function __construct(
        private readonly int $id,
        private readonly ?string $phone,
        private readonly ?string $email,
        private readonly array $extra,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @return array<string, mixed>
     */
    public function extra(): array
    {
        return $this->extra;
    }
}
