<?php

namespace App\Http\Controllers\Api;

use App\Application\Order\Command\ChangeOrderStatusFromFrontpadUseCase;
use App\Application\Order\Exception\OrderNotFoundForFrontpad;
use App\Application\Order\Exception\UnknownFrontpadStatus;
use App\Http\Controllers\Controller;
use App\Infrastructure\Integration\Frontpad\FrontpadLogger;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Frontpad требует HTTP 200 на любой метод при проверке hook_url в панели.
 * Реальная смена статуса — POST с action=change_status (по доке FP без secret).
 */
final class FrontpadWebhookController extends Controller
{
    public function __construct(
        private readonly ChangeOrderStatusFromFrontpadUseCase $changeOrderStatusFromFrontpad,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        if ($request->isMethod('post') && $request->input('action') === 'change_status') {
            return $this->changeStatus($request);
        }

        FrontpadLogger::info('Frontpad webhook probe', [
            'direction' => 'inbound',
            'operation' => 'webhook.probe',
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => FrontpadLogger::sanitizePayload($request->query()),
        ]);

        return response()->json(['ok' => true], 200);
    }

    private function changeStatus(Request $request): JsonResponse
    {
        $body = FrontpadLogger::sanitizePayload($request->all());

        FrontpadLogger::info('Frontpad webhook change_status received', [
            'direction' => 'inbound',
            'operation' => 'webhook.change_status',
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => FrontpadLogger::sanitizePayload($request->query()),
            'body' => $body,
        ]);

        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
            'status' => ['required', 'integer'],
            'datetime' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            FrontpadLogger::warning('Frontpad webhook validation failed', [
                'direction' => 'inbound',
                'operation' => 'webhook.change_status',
                'http_status' => 422,
                'errors' => $validator->errors()->toArray(),
            ]);

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
            FrontpadLogger::warning('Frontpad webhook order not found', [
                'direction' => 'inbound',
                'operation' => 'webhook.change_status',
                'http_status' => 404,
                'frontpad_order_id' => (string) $validated['order_id'],
                'frontpad_status' => (int) $validated['status'],
            ]);

            return response()->json(['message' => 'Not Found'], 404);
        } catch (UnknownFrontpadStatus) {
            FrontpadLogger::warning('Frontpad webhook unknown status', [
                'direction' => 'inbound',
                'operation' => 'webhook.change_status',
                'http_status' => 422,
                'frontpad_order_id' => (string) $validated['order_id'],
                'frontpad_status' => (int) $validated['status'],
            ]);

            return response()->json(['message' => 'Unprocessable Entity'], 422);
        }

        FrontpadLogger::info('Frontpad webhook change_status applied', [
            'direction' => 'inbound',
            'operation' => 'webhook.change_status',
            'http_status' => 200,
            'frontpad_order_id' => (string) $validated['order_id'],
            'frontpad_status' => (int) $validated['status'],
        ]);

        return response()->json(['ok' => true]);
    }
}
