<?php

namespace App\Http\Controllers;

use App\Enums\LegalDocumentType;
use App\Repositories\LegalDocumentRepository;
use Illuminate\Http\JsonResponse;

class LegalDocumentController extends Controller
{
    public function __construct(
        private LegalDocumentRepository $documents
    ) {
    }

    public function current(): JsonResponse
    {
        $payload = [];

        foreach (LegalDocumentType::ALL as $type) {
            $document = $this->documents->findCurrent($type);
            $payload[$type] = $document ? [
                'id' => $document->id,
                'version' => $document->version,
                'title' => $document->title,
                'url' => $document->publicUrl(),
            ] : null;
        }

        return response()->json($payload);
    }
}
