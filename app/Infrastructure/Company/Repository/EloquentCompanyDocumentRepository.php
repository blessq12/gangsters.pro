<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\CompanyDocument;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Mapper\CompanyDocumentMapper;
use App\Infrastructure\Company\Model\CompanyDocumentModel;
use Illuminate\Support\Str;

final class EloquentCompanyDocumentRepository implements CompanyDocumentRepository
{
    public function __construct(
        private readonly CompanyDocumentMapper $mapper,
    ) {}

    public function findById(int $id): ?CompanyDocument
    {
        $row = CompanyDocumentModel::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->find($id);

        return $row instanceof CompanyDocumentModel ? $this->mapper->toDomain($row) : null;
    }

    public function findAllOrdered(): array
    {
        return CompanyDocumentModel::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->orderBy('id')
            ->get()
            ->map(fn (CompanyDocumentModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listPage(int $offset, int $limit): array
    {
        return CompanyDocumentModel::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (CompanyDocumentModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countAll(): int
    {
        return (int) CompanyDocumentModel::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->count();
    }

    public function save(?int $id, string $name, ?string $content): CompanyDocument
    {
        $row = $id === null
            ? new CompanyDocumentModel([
                'company_id' => CompanyRepository::SINGLETON_ID,
                'slug' => $this->uniqueSlug($name),
            ])
            : CompanyDocumentModel::query()->find($id);

        if (! $row instanceof CompanyDocumentModel) {
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
        while (CompanyDocumentModel::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
