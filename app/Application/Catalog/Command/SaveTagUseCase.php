<?php

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\SaveTagDto;
use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Catalog\Repository\TagRepository;

final class SaveTagUseCase
{
    public function __construct(
        private readonly TagRepository $tags,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveTagDto $dto): array
    {
        $code = trim($dto->code);
        $label = trim($dto->label);
        $color = trim($dto->color);

        if ($code === '' || $label === '') {
            throw new \InvalidArgumentException('Нужны код и подпись тега.');
        }

        $tag = $this->tags->save(
            $dto->id,
            $code,
            $label,
            $color !== '' ? $color : '#999999',
            $dto->isActive,
        );

        return $this->presenter->tag($tag);
    }
}
