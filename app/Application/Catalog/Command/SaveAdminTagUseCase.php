<?php

namespace App\Application\Catalog\Command;

use App\Domain\Catalog\Repository\TagRepository;

final class SaveAdminTagUseCase
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(?int $id, string $code, string $label, string $color, bool $isActive): array
    {
        $code = trim($code);
        $label = trim($label);
        $color = trim($color);
        if ($code === '' || $label === '') {
            throw new \InvalidArgumentException('Нужны код и подпись тега.');
        }

        $tag = $this->tags->save($id, $code, $label, $color, $isActive);

        return [
            'id' => $tag->id(),
            'code' => $tag->code(),
            'label' => $tag->label(),
            'color' => $tag->color(),
            'is_active' => $tag->isActive(),
        ];
    }
}
