<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Document as DocumentEntity;
use App\Domain\SystemContent\Repository\DocumentRepository;
use App\Infrastructure\SystemContent\Model\SYS_Document;

final class EloquentDocumentRepository implements DocumentRepository
{
    public function findAllActiveOrdered(): array
    {
        return SYS_Document::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (SYS_Document $document) => $this->toEntity($document))
            ->all();
    }

    public function findAllOrdered(): array
    {
        return SYS_Document::query()
            ->orderBy('id')
            ->get()
            ->map(fn (SYS_Document $document) => $this->toEntity($document))
            ->all();
    }

    public function findById(int $id): ?DocumentEntity
    {
        $document = SYS_Document::query()->find($id);

        return $document !== null ? $this->toEntity($document) : null;
    }

    public function save(DocumentEntity $document): DocumentEntity
    {
        if ($document->id() > 0) {
            $model = SYS_Document::query()->findOrFail($document->id());
        } else {
            $model = new SYS_Document();
        }

        $model->fill([
            'key' => $document->key(),
            'title' => $document->title(),
            'content' => $document->content(),
            'is_active' => $document->isActive(),
        ]);

        $model->save();

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        SYS_Document::query()->whereKey($id)->delete();
    }

    private function toEntity(SYS_Document $document): DocumentEntity
    {
        return new DocumentEntity(
            id: (int) $document->id,
            key: (string) $document->key,
            title: (string) $document->title,
            content: $document->content,
            isActive: (bool) $document->is_active,
        );
    }
}
