<?php

namespace App\Repositories;

use App\Enums\LegalDocumentType;
use App\Models\LegalDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LegalDocumentRepository
{
    /**
     * Актуальный документ для публички = последняя версия по номеру.
     */
    public function findCurrent(string $type): ?LegalDocument
    {
        return LegalDocument::query()
            ->where('type', $type)
            ->orderByDesc('version')
            ->orderByDesc('id')
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
        $result = collect();

        foreach (LegalDocumentType::ALL as $type) {
            $document = $this->findCurrent($type);
            if ($document) {
                $result->put($type, $document);
            }
        }

        return $result;
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
                ->update(['is_current' => false]);

            $document = LegalDocument::query()->create([
                'type' => $type,
                'version' => $nextVersion,
                'title' => $title,
                'body_html' => $bodyHtml,
                'is_current' => true,
                'published_at' => now(),
            ]);

            $this->syncCurrentFlag($type);

            return $document->fresh();
        });
    }

    public function isCurrentDocument(int $documentId, string $type): bool
    {
        $current = $this->findCurrent($type);

        return $current !== null && (int) $current->id === (int) $documentId;
    }

    /**
     * is_current только у строки с максимальной version (на случай рассинхрона).
     */
    public function syncCurrentFlag(string $type): void
    {
        $latest = LegalDocument::query()
            ->where('type', $type)
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();

        if (!$latest) {
            return;
        }

        LegalDocument::query()
            ->where('type', $type)
            ->update(['is_current' => false]);

        LegalDocument::query()
            ->where('id', $latest->id)
            ->update(['is_current' => true]);
    }

    public function syncAllCurrentFlags(): void
    {
        foreach (LegalDocumentType::ALL as $type) {
            $this->syncCurrentFlag($type);
        }
    }
}
