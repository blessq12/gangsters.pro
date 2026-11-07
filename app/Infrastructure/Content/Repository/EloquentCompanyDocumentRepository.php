<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\CompanyDocument;
use App\Domain\Content\Repository\CompanyDocumentRepository;
use App\Domain\Content\Repository\CompanyRepository;
use App\Infrastructure\Content\Mapper\CompanyDocumentMapper;
use App\Infrastructure\Content\Model\CMP_CompanyDocument;
use Illuminate\Support\Str;

final class EloquentCompanyDocumentRepository implements CompanyDocumentRepository
{
    public function __construct(
        private readonly CompanyDocumentMapper $mapper,
    ) {}

    public function findAllOrdered(): array
    {
        return CMP_CompanyDocument::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->orderBy('id')
            ->get()
            ->map(fn (CMP_CompanyDocument $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(?int $id, string $name, ?string $content): CompanyDocument
    {
        $row = $id === null
            ? new CMP_CompanyDocument([
                'company_id' => CompanyRepository::SINGLETON_ID,
                'slug' => $this->uniqueSlug($name),
            ])
            : CMP_CompanyDocument::query()->find($id);

        if (! $row instanceof CMP_CompanyDocument) {
            throw new \InvalidArgumentException('Документ не найден.');
        }

        $row->fill([
            'name' => $name,
            'content' => $content,
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'document';
        }

        $slug = $base;
        $suffix = 2;
        while (CMP_CompanyDocument::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
