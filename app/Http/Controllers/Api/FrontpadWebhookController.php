<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\Command\ChangeOrderStatusFromFrontpadUseCase;
use App\Application\Order\Exception\OrderNotFoundForFrontpad;
use App\Application\Order\Exception\UnknownFrontpadStatus;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class FrontpadWebhookController extends Controller
{
    public function __construct(
        private readonly ChangeOrderStatusFromFrontpadUseCase $changeOrderStatusFromFrontpad,
    ) {}

    public function changeStatus(Request $request): JsonResponse
    {
        $configuredSecret = (string) config('frontpad.secret');
        $providedSecret = (string) ($request->query('secret') ?? $request->header('X-Frontpad-Secret') ?? '');

        if ($configuredSecret === '' || ! hash_equals($configuredSecret, $providedSecret)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($request->input('action') !== 'change_status') {
            return response()->json(['message' => 'Bad Request'], 400);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
            'status' => ['required', 'integer'],
            'datetime' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $changedAt = null;
        $datetime = $validated['datetime'] ?? null;
        if (is_string($datetime) && $datetime !== '') {
            $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $datetime);
            if ($parsed instanceof DateTimeImmutable) {
                $changedAt = $parsed;
            }
        }

        try {
            $this->changeOrderStatusFromFrontpad->handle(
                (string) $validated['order_id'],
                (int) $validated['status'],
                $changedAt,
            );
        } catch (OrderNotFoundForFrontpad) {
            return response()->json(['message' => 'Not Found'], 404);
        } catch (UnknownFrontpadStatus) {
            return response()->json(['message' => 'Unprocessable Entity'], 422);
        }

        return response()->json(['ok' => true]);
    }
}
