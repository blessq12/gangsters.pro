<?php

namespace App\Application\Operations\CartRules\Contracts;

use App\Application\Catalog\DTO\UpdateCartRuleFlagsDTO;

interface UpdateProductCartRuleFlagsContract
{
    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateCartRuleFlagsDTO $dto): array;
}
