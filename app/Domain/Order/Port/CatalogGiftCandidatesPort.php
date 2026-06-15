<?php

namespace App\Domain\Order\Port;

interface CatalogGiftCandidatesPort
{
    /**
     * @return list<CatalogGiftCandidate>
     */
    public function listActiveGiftCandidates(): array;
}
