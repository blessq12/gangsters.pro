<?php

namespace App\Infrastructure\Promotion\Mapper;

use App\Domain\Core\Order\Entity\ProductPromotionRole;
use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;
use App\Infrastructure\Promotion\Model\PRM_ProductRole;

final class ProductPromotionRoleMapper
{
    public function toDomain(PRM_ProductRole $row): ProductPromotionRole
    {
        $role = ProductPromotionRoleKind::tryFrom((string) $row->role);
        if ($role === null) {
            throw new \InvalidArgumentException('Неизвестная роль товара в акции: '.$row->role);
        }

        $paidTwin = $row->paid_twin_catalog_item_id !== null
            ? (int) $row->paid_twin_catalog_item_id
            : null;

        return new ProductPromotionRole(
            productId: (int) $row->catalog_item_id,
            role: $role,
            name: (string) $row->name_snapshot,
            priceKopecks: max(0, (int) $row->price_kopecks_snapshot),
            paidTwinProductId: $paidTwin !== null && $paidTwin > 0 ? $paidTwin : null,
        );
    }
}
