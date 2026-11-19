<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Company\Command\SaveBranchUseCase;
use App\Application\Company\Command\SaveCompanyDocumentUseCase;
use App\Application\Company\Command\SaveCompanyUseCase;
use App\Application\Company\Command\SaveKitchenUseCase;
use App\Application\Company\DTO\SaveBranchDto;
use App\Application\Company\DTO\SaveCompanyDocumentDto;
use App\Application\Company\DTO\SaveCompanyDto;
use App\Application\Company\DTO\SaveKitchenDto;
use App\Application\Company\Query\GetAdminCompanyUseCase;
use App\Application\Company\Query\ListAdminBranchesUseCase;
use App\Application\Company\Query\ListAdminDocumentsUseCase;
use App\Application\Company\Query\ListAdminKitchensUseCase;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class CompanyController extends Controller
{
    public function show(GetAdminCompanyUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute());
    }

    public function listBranches(Request $request, ListAdminBranchesUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function listKitchens(Request $request, ListAdminKitchensUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function listDocuments(Request $request, ListAdminDocumentsUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function store(Request $request, SaveCompanyUseCase $useCase): JsonResponse
    {
        return $this->saveCompany($request, $useCase, 201);
    }

    public function update(Request $request, SaveCompanyUseCase $useCase): JsonResponse
    {
        return $this->saveCompany($request, $useCase, 200);
    }

    public function storeBranch(Request $request, SaveBranchUseCase $useCase): JsonResponse
    {
        return $this->saveBranch($request, $useCase, null);
    }

    public function updateBranch(Request $request, int $id, SaveBranchUseCase $useCase): JsonResponse
    {
        return $this->saveBranch($request, $useCase, $id);
    }

    public function storeKitchen(Request $request, SaveKitchenUseCase $useCase): JsonResponse
    {
        return $this->saveKitchen($request, $useCase, null);
    }

    public function updateKitchen(Request $request, int $id, SaveKitchenUseCase $useCase): JsonResponse
    {
        return $this->saveKitchen($request, $useCase, $id);
    }

    public function storeDocument(Request $request, SaveCompanyDocumentUseCase $useCase): JsonResponse
    {
        return $this->saveDocument($request, $useCase, null);
    }

    public function updateDocument(Request $request, int $id, SaveCompanyDocumentUseCase $useCase): JsonResponse
    {
        return $this->saveDocument($request, $useCase, $id);
    }

    private function saveCompany(Request $request, SaveCompanyUseCase $useCase, int $status): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'socials' => ['sometimes', 'array'],
            'socials.telegram' => ['nullable', 'string', 'max:255'],
            'socials.vk' => ['nullable', 'string', 'max:255'],
            'socials.inst' => ['nullable', 'string', 'max:255'],
            'socials.site_url' => ['nullable', 'string', 'max:255'],
            'socials.whatsapp' => ['nullable', 'string', 'max:32'],
            'schedule' => ['sometimes', 'array'],
            'legal' => ['sometimes', 'array'],
            'legal.full_name' => ['nullable', 'string', 'max:255'],
            'legal.inn' => ['nullable', 'string', 'max:12'],
            'legal.ogrn' => ['nullable', 'string', 'max:15'],
        ]);

        try {
            $result = $useCase->execute(new SaveCompanyDto(
                name: $validated['name'],
                description: $validated['description'] ?? null,
                phone: $validated['phone'] ?? null,
                email: $validated['email'] ?? null,
                socials: is_array($validated['socials'] ?? null) ? $validated['socials'] : [],
                schedule: is_array($validated['schedule'] ?? null) ? $validated['schedule'] : [],
                legal: is_array($validated['legal'] ?? null) ? $validated['legal'] : [],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $status);
    }

    private function saveBranch(Request $request, SaveBranchUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['sometimes', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new SaveBranchDto(
                id: $id,
                companyId: (int) ($validated['company_id'] ?? CompanyRepository::SINGLETON_ID),
                name: $validated['name'],
                isActive: (bool) $validated['is_active'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveKitchen(Request $request, SaveKitchenUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'city' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'house' => ['nullable', 'string', 'max:64'],
            'comment' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        try {
            $result = $useCase->execute(new SaveKitchenDto(
                id: $id,
                branchId: (int) $validated['branch_id'],
                city: $validated['city'] ?? null,
                street: $validated['street'] ?? null,
                house: $validated['house'] ?? null,
                comment: $validated['comment'] ?? null,
                latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveDocument(Request $request, SaveCompanyDocumentUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        try {
            $result = $useCase->execute(new SaveCompanyDocumentDto(
                id: $id,
                name: $validated['name'],
                content: $validated['content'] ?? null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }
}
