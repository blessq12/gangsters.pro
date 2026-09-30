<?php

namespace App\Domain\Content\ValueObject;

/**
 * Соцсети и внешние ссылки компании (JSON `socials`).
 */
final readonly class CompanySocials
{
    public function __construct(
        private ?string $telegram,
        private ?string $vk,
        private ?string $inst,
        private ?string $siteUrl,
        private ?string $whatsapp,
    ) {}

    public function telegram(): ?string
    {
        return $this->telegram;
    }

    public function vk(): ?string
    {
        return $this->vk;
    }

    public function inst(): ?string
    {
        return $this->inst;
    }

    public function siteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function whatsapp(): ?string
    {
        return $this->whatsapp;
    }

    /**
     * @return array{telegram: ?string, vk: ?string, inst: ?string, site_url: ?string, whatsapp: ?string}
     */
    public function toArray(): array
    {
        return [
            'telegram' => $this->telegram,
            'vk' => $this->vk,
            'inst' => $this->inst,
            'site_url' => $this->siteUrl,
            'whatsapp' => $this->whatsapp,
        ];
    }
}
