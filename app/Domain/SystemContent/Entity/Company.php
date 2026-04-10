<?php

namespace App\Domain\SystemContent\Entity;

final class Company
{
    public function __construct(
        private readonly int $id,
        private readonly ?string $name,
        private readonly ?string $brandName,
        private readonly ?string $description,
        private readonly ?string $tagline,
        private readonly ?string $country,
        private readonly ?string $state,
        private readonly ?string $city,
        private readonly ?string $street,
        private readonly ?string $house,
        private readonly ?string $addressComment,
        private readonly ?string $cityCoverage,
        private readonly ?string $phone,
        private readonly ?string $phoneAdditional,
        private readonly ?string $supportPhone,
        private readonly ?string $whatsappPhone,
        private readonly ?string $emailAddress,
        private readonly ?string $publicEmail,
        private readonly ?string $workHours,
        private readonly ?string $deliveryHours,
        /** @var array<int, array<string, mixed>>|null строки: day, work, is_day_off (legacy в БД мог содержать delivery) */
        private readonly ?array $workSchedule,
        private readonly ?int $minOrderAmountKopecks,
        private readonly ?int $deliveryFeeKopecks,
        private readonly ?int $averageDeliveryTimeMinutes,
        private readonly ?string $telegram,
        private readonly ?string $siteUrl,
        /** @var array<int, array<string, mixed>>|null */
        private readonly ?array $socialLinks,
        private readonly ?string $vk,
        private readonly ?string $inst,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function name(): ?string
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

    public function country(): ?string
    {
        return $this->country;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function street(): ?string
    {
        return $this->street;
    }

    public function house(): ?string
    {
        return $this->house;
    }

    public function addressComment(): ?string
    {
        return $this->addressComment;
    }

    public function cityCoverage(): ?string
    {
        return $this->cityCoverage;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function phoneAdditional(): ?string
    {
        return $this->phoneAdditional;
    }

    public function supportPhone(): ?string
    {
        return $this->supportPhone;
    }

    public function whatsappPhone(): ?string
    {
        return $this->whatsappPhone;
    }

    public function emailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function publicEmail(): ?string
    {
        return $this->publicEmail;
    }

    public function workHours(): ?string
    {
        return $this->workHours;
    }

    public function deliveryHours(): ?string
    {
        return $this->deliveryHours;
    }

    /** @return array<int, array<string, mixed>>|null по дням: day, work, is_day_off */
    public function workSchedule(): ?array
    {
        return $this->workSchedule;
    }

    public function minOrderAmountKopecks(): ?int
    {
        return $this->minOrderAmountKopecks;
    }

    public function deliveryFeeKopecks(): ?int
    {
        return $this->deliveryFeeKopecks;
    }

    public function averageDeliveryTimeMinutes(): ?int
    {
        return $this->averageDeliveryTimeMinutes;
    }

    public function telegram(): ?string
    {
        return $this->telegram;
    }

    public function siteUrl(): ?string
    {
        return $this->siteUrl;
    }

    /** @return array<int, array<string, mixed>>|null */
    public function socialLinks(): ?array
    {
        return $this->socialLinks;
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
