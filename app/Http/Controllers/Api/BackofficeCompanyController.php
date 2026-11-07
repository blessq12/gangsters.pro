<?php

namespace App\Http\Controllers\Api;

use App\Application\Content\Command\SaveCompanyDocumentUseCase;
use App\Application\Content\Command\SaveOwnerCompanyUseCase;
use App\Application\Content\Query\GetOwnerCompanyUseCase;
use App\Application\Content\Query\ListCompanyDocumentsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BackofficeCompanyController extends Controller
{
    public function __construct(
        private readonly GetOwnerCompanyUseCase $company,
        private readonly SaveOwnerCompanyUseCase $saveCompany,
        private readonly ListCompanyDocumentsUseCase $documents,
        private readonly SaveCompanyDocumentUseCase $saveDocument,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json(['company' => $this->company->execute()]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'string', 'max:255'],
            'socials' => ['nullable', 'array'],
            'socials.telegram' => ['nullable', 'string', 'max:255'],
            'socials.vk' => ['nullable', 'string', 'max:255'],
            'socials.inst' => ['nullable', 'string', 'max:255'],
            'socials.site_url' => ['nullable', 'string', 'max:255'],
            'socials.whatsapp' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'array'],
            'legal' => ['nullable', 'array'],
            'legal.full_name' => ['nullable', 'string', 'max:255'],
            'legal.inn' => ['nullable', 'string', 'max:32'],
            'legal.ogrn' => ['nullable', 'string', 'max:32'],
        ]);

        try {
            return response()->json(['company' => $this->saveCompany->execute($validated)]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function documents(): JsonResponse
    {
        return response()->json(['documents' => $this->documents->execute()]);
    }

    public function storeDocument(Request $request): JsonResponse
    {
        return $this->writeDocument($request, null);
    }

    public function updateDocument(Request $request, int $document): JsonResponse
    {
        return $this->writeDocument($request, $document);
    }

    private function writeDocument(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        try {
            $saved = $this->saveDocument->execute($id, $validated['name'], $validated['content'] ?? null);

            return response()->json(['document' => $saved], $id === null ? 201 : 200);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
