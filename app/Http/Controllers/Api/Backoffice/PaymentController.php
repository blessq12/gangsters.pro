<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Payment\Command\MarkPaymentPaidUseCase;
use App\Application\Payment\Command\RefundPaymentUseCase;
use App\Application\Payment\Query\GetPaymentSummaryUseCase;
use App\Application\Payment\Query\GetPaymentUseCase;
use App\Application\Payment\Query\ListAdminPaymentsUseCase;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class PaymentController extends Controller
{
    public function index(Request $request, ListAdminPaymentsUseCase $useCase): JsonResponse
    {
        [$from, $to] = $this->parsePeriod($request);

        return response()->json($useCase->execute(
            createdFrom: $from,
            createdTo: $to,
            status: $this->queryString($request, 'status'),
            method: $this->queryString($request, 'method'),
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function summary(Request $request, GetPaymentSummaryUseCase $useCase): JsonResponse
    {
        [$from, $to] = $this->parsePeriod($request);

        return response()->json($useCase->execute(
            createdFrom: $from,
            createdTo: $to,
            status: $this->queryString($request, 'status'),
            method: $this->queryString($request, 'method'),
        ));
    }

    public function show(int $id, GetPaymentUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function markPaid(int $id, MarkPaymentPaidUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function refund(int $id, RefundPaymentUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    private function queryString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }

    /**
     * @return array{0: DateTimeImmutable|null, 1: DateTimeImmutable|null}
     */
    private function parsePeriod(Request $request): array
    {
        $fromRaw = $request->query('from');
        $toRaw = $request->query('to');

        $from = is_string($fromRaw) && trim($fromRaw) !== ''
            ? $this->parseDayStart(trim($fromRaw))
            : null;
        $to = is_string($toRaw) && trim($toRaw) !== ''
            ? $this->parseDayEnd(trim($toRaw))
            : null;

        return [$from, $to];
    }

    private function parseDayStart(string $value): DateTimeImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return new DateTimeImmutable($value.' 00:00:00');
        }

        return new DateTimeImmutable($value);
    }

    private function parseDayEnd(string $value): DateTimeImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return new DateTimeImmutable($value.' 23:59:59');
        }

        return new DateTimeImmutable($value);
    }
}
