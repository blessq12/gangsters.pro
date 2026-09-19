<?php

namespace App\Repositories;

use App\Enums\LegalDocumentType;
use App\Models\LegalDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LegalDocumentRepository
{
    public function findCurrent(string $type): ?LegalDocument
    {
        return LegalDocument::query()
            ->where('type', $type)
            ->where('is_current', true)
            ->first();
    }

    public function findCurrentOrFail(string $type): LegalDocument
    {
        $document = $this->findCurrent($type);

        if (!$document) {
            throw new InvalidArgumentException("Текущий документ типа {$type} не найден");
        }

        return $document;
    }

    /**
     * @return Collection<string, LegalDocument>
     */
    public function allCurrent(): Collection
    {
        return LegalDocument::query()
            ->where('is_current', true)
            ->whereIn('type', LegalDocumentType::ALL)
            ->get()
            ->keyBy('type');
    }

    public function publishNewVersion(string $type, string $title, string $bodyHtml): LegalDocument
    {
        if (!LegalDocumentType::isValid($type)) {
            throw new InvalidArgumentException("Неизвестный тип документа: {$type}");
        }

        return DB::transaction(function () use ($type, $title, $bodyHtml) {
            $nextVersion = ((int) LegalDocument::query()->where('type', $type)->max('version')) + 1;

            LegalDocument::query()
                ->where('type', $type)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            return LegalDocument::query()->create([
                'type' => $type,
                'version' => $nextVersion,
                'title' => $title,
                'body_html' => $bodyHtml,
                'is_current' => true,
                'published_at' => now(),
            ]);
        });
    }

    public function isCurrentDocument(int $documentId, string $type): bool
    {
        return LegalDocument::query()
            ->where('id', $documentId)
            ->where('type', $type)
            ->where('is_current', true)
            ->exists();
    }
}
