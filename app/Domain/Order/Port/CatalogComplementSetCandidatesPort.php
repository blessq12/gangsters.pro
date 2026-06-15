<?php

namespace App\Domain\Order\Port;

interface CatalogComplementSetCandidatesPort
{
    /**
     * @return list<CatalogComplementSetCandidate>
     */
    public function listActiveComplementSetCandidates(): array;
}
