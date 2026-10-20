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
 * Реальная смена статуса — только POST с action=change_status + secret.
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

        if (! $this->isAuthorized($request)) {
            $configuredSecret = trim((string) config('frontpad.secret'));

            FrontpadLogger::warning('Frontpad webhook unauthorized', [
                'direction' => 'inbound',
                'operation' => 'webhook.change_status',
                'http_status' => 401,
                'secret_sources' => $this->secretSourceFlags($request),
                'secret_lengths' => [
                    'configured' => strlen($configuredSecret),
                    'body' => $this->secretLength($request->input('secret')),
                    'query' => $this->secretLength($request->query('secret')),
                    'header' => $this->secretLength($request->header('X-Frontpad-Secret')),
                ],
            ]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

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

    /**
     * Frontpad шлёт secret в form-body и/или в query hook_url.
     * Принимаем, если хоть один кандидат совпал с FRONTPAD_SECRET.
     */
    private function isAuthorized(Request $request): bool
    {
        $configuredSecret = trim((string) config('frontpad.secret'));
        if ($configuredSecret === '') {
            return false;
        }

        foreach ($this->secretCandidates($request) as $candidate) {
            if (hash_equals($configuredSecret, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function secretCandidates(Request $request): array
    {
        $raw = [
            $request->input('secret'),
            $request->query('secret'),
            $request->header('X-Frontpad-Secret'),
        ];

        $candidates = [];
        foreach ($raw as $value) {
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                continue;
            }

            $trimmed = trim((string) $value);
            if ($trimmed !== '') {
                $candidates[] = $trimmed;
            }
        }

        return array_values(array_unique($candidates));
    }

    /**
     * @return array{body: bool, query: bool, header: bool}
     */
    private function secretSourceFlags(Request $request): array
    {
        $body = $request->input('secret');
        $query = $request->query('secret');
        $header = $request->header('X-Frontpad-Secret');

        return [
            'body' => $this->nonEmptySecret($body),
            'query' => $this->nonEmptySecret($query),
            'header' => $this->nonEmptySecret($header),
        ];
    }

    private function nonEmptySecret(mixed $value): bool
    {
        return $this->secretLength($value) > 0;
    }

    private function secretLength(mixed $value): int
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return 0;
        }

        return strlen(trim((string) $value));
    }
}
