<?php

namespace App\Domain\Checkout\Port;

interface CatalogGiftCandidatesPort
{
    /**
     * @return list<CatalogGiftCandidate>
     */
    public function listActiveGiftCandidates(): array;
}
