<?php

namespace App\Infrastructure\Promotion\Mapper;

use App\Domain\Promotion\Entity\ProductPromotionRole;
use App\Domain\Promotion\Enum\ProductPromotionRoleKind;
use App\Infrastructure\Promotion\Model\PRM_ProductRole;

final class ProductPromotionRoleMapper
{
    public function toDomain(PRM_ProductRole $row): ProductPromotionRole
    {
        $role = ProductPromotionRoleKind::tryFrom((string) $row->role);
        if ($role === null) {
            throw new \InvalidArgumentException('Неизвестная роль товара в акции: '.$row->role);
        }

        $paidTwin = $row->paid_twin_product_id !== null ? (int) $row->paid_twin_product_id : null;

        return new ProductPromotionRole(
            productId: (int) $row->product_id,
            role: $role,
            name: (string) $row->name,
            priceKopecks: max(0, (int) $row->price_kopecks),
            paidTwinProductId: $paidTwin !== null && $paidTwin > 0 ? $paidTwin : null,
        );
    }
}
