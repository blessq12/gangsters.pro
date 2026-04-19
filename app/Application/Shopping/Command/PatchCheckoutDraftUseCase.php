<?php

namespace App\Application\Shopping\Command;

use App\Application\Shopping\Presenter\ShoppingStatePresenter;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;

final class PatchCheckoutDraftUseCase
{
    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
        private readonly ShoppingStatePresenter $presenter,
    ) {
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    public function execute(ShoppingSession $session, array $draft): array
    {
        $current = $session->getCheckoutDraft() ?? [];
        $merged = array_replace_recursive($current, $draft);
        $session->setCheckoutDraft($merged === [] ? null : $merged);
        $this->sessions->save($session);

        return $this->presenter->present($session);
    }
}
