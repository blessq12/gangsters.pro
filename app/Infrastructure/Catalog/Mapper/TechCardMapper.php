<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Supporting\Catalog\Entity\TechCard;
use App\Domain\Supporting\Catalog\ValueObject\TechCardLine;
use App\Infrastructure\Catalog\Model\TechCardModel;
use App\Infrastructure\Catalog\Model\TechCardLineModel;

final class TechCardMapper
{
    public function toDomain(TechCardModel $row): TechCard
    {
        $lines = [];
        foreach ($row->lines as $lineRow) {
            if (! $lineRow instanceof TechCardLineModel) {
                continue;
            }
            $lines[] = new TechCardLine(
                ingredientRef: (string) $lineRow->ingredient_ref,
                quantity: (float) $lineRow->quantity,
                unit: (string) $lineRow->unit,
            );
        }

        return TechCard::forProduct((int) $row->product_id, $lines);
    }
}
