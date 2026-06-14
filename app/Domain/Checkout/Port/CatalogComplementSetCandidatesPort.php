<?php

namespace App\Domain\Checkout\Port;

interface CatalogComplementSetCandidatesPort
{
    /**
     * @return list<CatalogComplementSetCandidate>
     */
    public function listActiveComplementSetCandidates(): array;
}
