<?php

namespace App\Application\Company\DTO;

/**
 * Клонирование per-branch настроек: меню / политики+роли / доставка.
 * mode: overwrite | skip_existing.
 */
final readonly class CloneBranchSettingsDto
{
    /**
     * @param  list<int>  $targetBranchIds
     */
    public function __construct(
        public int $sourceBranchId,
        public array $targetBranchIds,
        public bool $copyMenu,
        public bool $copyPromotions,
        public bool $copyDelivery,
        public string $mode = 'overwrite',
    ) {}
}
