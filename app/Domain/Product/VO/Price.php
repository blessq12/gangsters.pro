<?php

namespace App\Domain\Product\VO;

final class Price
{
    public function __construct(
        private int $amount, // в минимальных единицах (копейки)
        private CustomerStatus $customerStatus,
        private bool $isDefault = false,
    ) {
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function customerStatus(): CustomerStatus
    {
        return $this->customerStatus;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}

