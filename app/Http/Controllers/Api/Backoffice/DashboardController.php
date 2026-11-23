<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Dashboard\Query\GetDashboardSummaryUseCase;
use App\Domain\Generic\Staff\Enum\StaffRole;
use App\Http\Controllers\Controller;
use App\Infrastructure\Staff\Model\STF_Employee;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function summary(Request $request, GetDashboardSummaryUseCase $useCase): JsonResponse
    {
        [$from, $to] = $this->parsePeriod($request);
        $branchId = $this->resolveBranchId($request);

        if ($branchId === false) {
            return response()->json(['message' => 'У сотрудника не указан филиал смены'], 422);
        }

        return response()->json($useCase->execute(
            createdFrom: $from,
            createdTo: $to,
            branchId: $branchId,
        ));
    }

    /**
     * @return int|null|false null = все филиалы; false = ошибка для admin без филиала
     */
    private function resolveBranchId(Request $request): int|null|false
    {
        $user = $request->user('sanctum');
        if ($user instanceof STF_Employee && (string) $user->role === StaffRole::Administrator->value) {
            $id = (int) ($user->branch_id ?? 0);

            return $id > 0 ? $id : false;
        }

        $raw = $request->query('branch_id');
        if ($raw === null || $raw === '') {
            return null;
        }
        $id = (int) $raw;

        return $id > 0 ? $id : null;
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
