<?php

namespace App\Application\Marketing\DTO;

final readonly class SaveContactBlockDto
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?int $id,
        public ?string $phone,
        public ?string $email,
        public array $extra,
    ) {}
}
