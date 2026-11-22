<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Supporting\Company\Entity\BranchSet;
use App\Domain\Supporting\Company\ValueObject\BranchSetLine;
use App\Infrastructure\Company\Model\CMP_BranchSet;
use App\Infrastructure\Company\Model\CMP_BranchSetLine;

final class BranchSetMapper
{
    /**
     * @param  list<int>  $tagIds
     */
    public function toDomain(CMP_BranchSet $row, array $tagIds = []): BranchSet
    {
        $lines = [];
        foreach ($row->lines as $line) {
            $lines[] = $this->lineToDomain($line);
        }

        return new BranchSet(
            id: (int) $row->id,
            branchId: (int) $row->branch_id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            description: $row->description !== null ? (string) $row->description : null,
            isAvailable: (bool) $row->is_available,
            priceKopecks: (int) $row->price_kopecks,
            status: (string) $row->status,
            lines: $lines,
            tagIds: $tagIds,
        );
    }

    public function lineToDomain(CMP_BranchSetLine $row): BranchSetLine
    {
        return new BranchSetLine(
            catalogItemId: (int) $row->catalog_item_id,
            quantity: (int) $row->quantity,
        );
    }
}
