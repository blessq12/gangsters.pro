<?php

namespace App\Application\Company\DTO;

final readonly class SaveCompanyDto
{
    /**
     * @param  array<string, mixed>  $socials
     * @param  array<string, mixed>  $schedule
     * @param  array<string, mixed>  $legal
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public ?string $phone,
        public ?string $email,
        public array $socials,
        public array $schedule,
        public array $legal,
    ) {}
}
