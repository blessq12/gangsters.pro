<?php

namespace App\Services;

use App\Enums\LegalConsentType;
use App\Enums\LegalDocumentType;
use App\Models\LegalConsent;
use App\Models\Order;
use App\Models\User;
use App\Repositories\LegalDocumentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LegalConsentService
{
    public function __construct(
        private LegalDocumentRepository $documents
    ) {
    }

    /**
     * @param array{offer_document_id:int, pdn_document_id:int} $consents
     */
    public function assertOrderConsents(array $consents): void
    {
        $offerId = (int) ($consents['offer_document_id'] ?? 0);
        $pdnId = (int) ($consents['pdn_document_id'] ?? 0);

        if (!$this->documents->isCurrentDocument($offerId, LegalDocumentType::OFFER)) {
            throw new InvalidArgumentException('Необходимо принять актуальную публичную оферту');
        }

        if (!$this->documents->isCurrentDocument($pdnId, LegalDocumentType::PDN_CONSENT)) {
            throw new InvalidArgumentException('Необходимо дать актуальное согласие на обработку ПДн');
        }
    }

    public function assertPdnConsent(int $documentId): void
    {
        if (!$this->documents->isCurrentDocument($documentId, LegalDocumentType::PDN_CONSENT)) {
            throw new InvalidArgumentException('Необходимо дать актуальное согласие на обработку ПДн');
        }
    }

    /**
     * @param array{offer_document_id:int, pdn_document_id:int} $consents
     */
    public function recordOrderConsents(Order $order, array $consents, Request $request): void
    {
        $this->assertOrderConsents($consents);

        DB::transaction(function () use ($order, $consents, $request) {
            $this->storeConsent(
                LegalConsentType::OFFER,
                (int) $consents['offer_document_id'],
                $request,
                $order->user_id,
                $order->id
            );
            $this->storeConsent(
                LegalConsentType::PDN,
                (int) $consents['pdn_document_id'],
                $request,
                $order->user_id,
                $order->id
            );
        });
    }

    public function recordRegisterPdnConsent(User $user, int $documentId, Request $request): void
    {
        $this->assertPdnConsent($documentId);

        $this->storeConsent(
            LegalConsentType::PDN,
            $documentId,
            $request,
            $user->id,
            null
        );
    }

    private function storeConsent(
        string $consentType,
        int $documentId,
        Request $request,
        ?int $userId,
        ?int $orderId
    ): LegalConsent {
        return LegalConsent::query()->create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'legal_document_id' => $documentId,
            'consent_type' => $consentType,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'accepted_at' => now(),
        ]);
    }
}
