<?php

namespace App\Application\Company\Command;

use App\Application\Company\DTO\SaveCompanyDocumentDto;
use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;

final class SaveCompanyDocumentUseCase
{
    public function __construct(
        private readonly CompanyDocumentRepository $documents,
        private readonly CompanyPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveCompanyDocumentDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название документа.');
        }

        $content = trim((string) $dto->content);
        $document = $this->documents->save(
            $dto->id,
            $name,
            $content !== '' ? $content : null,
        );

        return $this->presenter->document($document);
    }
}
