<?php

namespace App\Domain\SystemContent\Entity;

final class Company
{
    public function __construct(
        private readonly int $id,
        private readonly ?string $name,
        private readonly ?string $description,
        private readonly ?string $country,
        private readonly ?string $state,
        private readonly ?string $city,
        private readonly ?string $street,
        private readonly ?string $house,
        private readonly ?string $phone,
        private readonly ?string $phoneAdditional,
        private readonly ?string $emailAddress,
        private readonly ?string $logo,
    ) {
    }

    public function id(): int { return $this->id; }
    public function name(): ?string { return $this->name; }
    public function description(): ?string { return $this->description; }
    public function country(): ?string { return $this->country; }
    public function state(): ?string { return $this->state; }
    public function city(): ?string { return $this->city; }
    public function street(): ?string { return $this->street; }
    public function house(): ?string { return $this->house; }
    public function phone(): ?string { return $this->phone; }
    public function phoneAdditional(): ?string { return $this->phoneAdditional; }
    public function emailAddress(): ?string { return $this->emailAddress; }
    public function logo(): ?string { return $this->logo; }
}

