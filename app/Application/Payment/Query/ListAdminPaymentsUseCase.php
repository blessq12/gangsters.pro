<?php

namespace App\Application\Payment\Query;

use App\Application\Payment\Presenter\PaymentPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;
use DateTimeImmutable;

final class ListAdminPaymentsUseCase
{
    public function __construct(
        private readonly PaymentRepository $payments,
        private readonly PaymentPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
        ?int $branchId = null,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
    ): array {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);
        $statusFilter = $status !== null && trim($status) !== '' ? trim($status) : null;
        $methodFilter = $method !== null && trim($method) !== '' ? trim($method) : null;
        $branchFilter = $branchId !== null && $branchId > 0 ? $branchId : null;

        $items = [];
        foreach ($this->payments->listPage(
            $offset,
            $limit,
            $createdFrom,
            $createdTo,
            $statusFilter,
            $methodFilter,
            $branchFilter,
        ) as $payment) {
            $items[] = $this->presenter->present($payment);
        }

        return PageParams::wrap(
            $items,
            $this->payments->countFiltered(
                $createdFrom,
                $createdTo,
                $statusFilter,
                $methodFilter,
                $branchFilter,
            ),
            $offset,
            $limit,
        );
    }
}
