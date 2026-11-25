<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Order\Checkout\CheckoutPayload;
use App\Application\Order\Command\AcceptOrderUseCase;
use App\Application\Order\Command\AddOrderNoteUseCase;
use App\Application\Order\Command\CancelOrderUseCase;
use App\Application\Order\Command\CompletePickupOrderUseCase;
use App\Application\Order\Command\CreateOrderUseCase;
use App\Application\Order\Command\SaveOrderCommercialRulesUseCase;
use App\Application\Order\Command\UpdateAdminOrderUseCase;
use App\Application\Order\DTO\AcceptOrderDto;
use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\SaveOrderCommercialRulesDto;
use App\Application\Order\DTO\UpdateAdminOrderDto;
use App\Application\Order\Query\GetAdminOrderUseCase;
use App\Application\Order\Query\GetOrderCommercialRulesUseCase;
use App\Application\Order\Query\ListAdminOrdersUseCase;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Http\Controllers\Controller;
use App\Infrastructure\Staff\Model\EmployeeModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

final class OrderController extends Controller
{
    public function index(Request $request, ListAdminOrdersUseCase $useCase): JsonResponse
    {
        $status = $request->query('status');
        $q = $request->query('q');
        $branchId = $request->query('branch_id');
        $deliveryMethod = $request->query('delivery_method');
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 20);

        return response()->json($useCase->execute(
            status: is_string($status) ? $status : null,
            offset: $offset,
            limit: $limit,
            q: is_string($q) ? $q : null,
            branchId: is_numeric($branchId) ? (int) $branchId : null,
            deliveryMethod: is_string($deliveryMethod) ? $deliveryMethod : null,
        ));
    }

    public function show(int $id, GetAdminOrderUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function quote(Request $request, QuoteOrderUseCase $useCase): JsonResponse
    {
        $validated = Validator::make(
            CheckoutPayload::normalize($request->all()),
            CheckoutPayload::rules(),
        )->validate();

        try {
            $result = $useCase->execute(CheckoutPayload::toDto($validated));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function store(Request $request, CreateOrderUseCase $useCase): JsonResponse
    {
        $validated = Validator::make(
            [
                ...CheckoutPayload::normalize($request->all()),
                'client_request_id' => $request->input('client_request_id'),
            ],
            [
                ...CheckoutPayload::rules(),
                'client_request_id' => ['required', 'string', 'max:128'],
            ],
        )->validate();

        try {
            $result = $useCase->execute(new CreateOrderDto(
                clientRequestId: $validated['client_request_id'],
                checkout: CheckoutPayload::toDto($validated),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    public function update(
        Request $request,
        int $id,
        UpdateAdminOrderUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'client' => ['sometimes', 'array'],
            'client.name' => ['sometimes', 'string', 'max:255'],
            'client.phone' => ['sometimes', 'string', 'max:32'],
            'client.id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'delivery' => ['sometimes', 'array'],
            'delivery.method' => ['sometimes', 'string', 'in:pickup,courier'],
            'delivery.zone_id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'delivery.fee_kopecks' => ['sometimes', 'integer', 'min:0'],
            'delivery.address' => ['sometimes', 'nullable', 'array'],
            'delivery.address.street' => ['sometimes', 'string', 'max:255'],
            'delivery.address.house' => ['sometimes', 'string', 'max:64'],
            'delivery.address.entrance' => ['sometimes', 'nullable', 'string', 'max:64'],
            'delivery.address.floor' => ['sometimes', 'nullable', 'string', 'max:64'],
            'delivery.address.apartment' => ['sometimes', 'nullable', 'string', 'max:64'],
            'delivery.address.comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'delivery.address.lat' => ['sometimes', 'nullable', 'numeric'],
            'delivery.address.lng' => ['sometimes', 'nullable', 'numeric'],
            'payment_method' => ['sometimes', 'string', 'max:32'],
            'change_from_rubles' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'scheduled_at' => ['sometimes', 'nullable', 'string', 'max:64'],
            'lines' => ['sometimes', 'array', 'min:1'],
            'lines.*.product_id' => ['required_with:lines', 'integer', 'min:1'],
            'lines.*.name' => ['required_with:lines', 'string', 'max:255'],
            'lines.*.unit_price_kopecks' => ['required_with:lines', 'integer', 'min:0'],
            'lines.*.quantity' => ['required_with:lines', 'integer', 'min:1'],
            'lines.*.line_type' => ['required_with:lines', 'string', 'max:32'],
        ]);

        $user = $request->user('sanctum');
        $authorId = $user instanceof EmployeeModel ? (int) $user->id : null;

        try {
            $result = $useCase->execute(new UpdateAdminOrderDto(
                orderId: $id,
                authorEmployeeId: $authorId,
                client: is_array($validated['client'] ?? null) ? $validated['client'] : null,
                delivery: is_array($validated['delivery'] ?? null) ? $validated['delivery'] : null,
                paymentMethod: array_key_exists('payment_method', $validated)
                    ? (string) $validated['payment_method']
                    : null,
                changeFromRubles: array_key_exists('change_from_rubles', $validated)
                    ? ($validated['change_from_rubles'] !== null ? (int) $validated['change_from_rubles'] : null)
                    : null,
                changeFromRublesProvided: array_key_exists('change_from_rubles', $validated),
                lines: is_array($validated['lines'] ?? null) ? $validated['lines'] : null,
                scheduledAt: array_key_exists('scheduled_at', $validated)
                    ? ($validated['scheduled_at'] !== null ? (string) $validated['scheduled_at'] : null)
                    : null,
                scheduledAtProvided: array_key_exists('scheduled_at', $validated),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function accept(int $id, AcceptOrderUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute(new AcceptOrderDto(
                orderId: $id,
            )));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(
        Request $request,
        int $id,
        CancelOrderUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'target_status' => ['required', 'string', 'max:64'],
            'actor_role' => ['nullable', 'string', 'max:64'],
        ]);

        if ($validated['target_status'] !== OrderStatus::Cancelled->value) {
            return response()->json([
                'message' => 'Этот статус выставляется доменным событием. Через эту команду можно только отменить заказ.',
            ], 422);
        }

        try {
            $result = $useCase->execute($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function completePickup(int $id, CompletePickupOrderUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function addNote(
        Request $request,
        int $id,
        AddOrderNoteUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $user = $request->user('sanctum');
        $authorId = $user instanceof EmployeeModel ? (int) $user->id : null;

        try {
            $note = $useCase->execute($id, $validated['body'], $authorId);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['note' => $note], 201);
    }

    public function showCommercialRules(Request $request, GetOrderCommercialRulesUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute((int) $validated['branch_id']));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function storeCommercialRules(Request $request, SaveOrderCommercialRulesUseCase $useCase): JsonResponse
    {
        return $this->saveCommercialRules($request, $useCase, 201);
    }

    public function updateCommercialRules(Request $request, SaveOrderCommercialRulesUseCase $useCase): JsonResponse
    {
        return $this->saveCommercialRules($request, $useCase, 200);
    }

    private function saveCommercialRules(
        Request $request,
        SaveOrderCommercialRulesUseCase $useCase,
        int $status,
    ): JsonResponse {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'gift_pickup_min_order_kopecks' => ['required', 'integer', 'min:0'],
            'gift_courier_min_order_kopecks' => ['required', 'integer', 'min:0'],
            'gift_courier_weekdays' => ['sometimes', 'array'],
            'gift_courier_weekdays.*' => ['integer', 'min:1', 'max:7'],
            'gift_benefit_active' => ['required', 'boolean'],
            'delivery_free_threshold_kopecks' => ['required', 'integer', 'min:0'],
            'delivery_benefit_active' => ['required', 'boolean'],
            'complement_set_benefit_active' => ['required', 'boolean'],
            'complement_set_rolls_per_set' => ['required', 'integer', 'min:1'],
            'complement_set_max_sets' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $useCase->execute(new SaveOrderCommercialRulesDto(
                branchId: (int) $validated['branch_id'],
                giftPickupMinOrderKopecks: (int) $validated['gift_pickup_min_order_kopecks'],
                giftCourierMinOrderKopecks: (int) $validated['gift_courier_min_order_kopecks'],
                giftCourierWeekdays: is_array($validated['gift_courier_weekdays'] ?? null)
                    ? $validated['gift_courier_weekdays']
                    : [],
                giftBenefitActive: (bool) $validated['gift_benefit_active'],
                deliveryFreeThresholdKopecks: (int) $validated['delivery_free_threshold_kopecks'],
                deliveryBenefitActive: (bool) $validated['delivery_benefit_active'],
                complementSetBenefitActive: (bool) $validated['complement_set_benefit_active'],
                complementSetRollsPerSet: (int) $validated['complement_set_rolls_per_set'],
                complementSetMaxSets: (int) $validated['complement_set_max_sets'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $status);
    }
}
