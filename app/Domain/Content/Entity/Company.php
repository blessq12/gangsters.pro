<?php

namespace App\Domain\Content\Entity;

use App\Domain\Content\ValueObject\CompanyContact;
use App\Domain\Content\ValueObject\CompanySchedule;

/**
 * Публичный профиль компании.
 */
final class Company
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly ?string $brandName,
        private readonly ?string $description,
        private readonly ?string $tagline,
        private readonly CompanyContact $contact,
        private readonly CompanySchedule $schedule,
        private readonly ?string $logo,
        private readonly ?string $telegram,
        private readonly ?string $siteUrl,
        private readonly ?string $vk,
        private readonly ?string $inst,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function brandName(): ?string
    {
        return $this->brandName;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function tagline(): ?string
    {
        return $this->tagline;
    }

    public function contact(): CompanyContact
    {
        return $this->contact;
    }

    public function schedule(): CompanySchedule
    {
        return $this->schedule;
    }

    public function logo(): ?string
    {
        return $this->logo;
    }

    public function telegram(): ?string
    {
        return $this->telegram;
    }

    public function siteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function vk(): ?string
    {
        return $this->vk;
    }

    public function inst(): ?string
    {
        return $this->inst;
    }
}
