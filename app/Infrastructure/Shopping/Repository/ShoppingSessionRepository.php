<?php

namespace App\Infrastructure\Shopping\Repository;

use App\Domain\Shopping\Entities\CartLine;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartLine;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCheckoutDraft;
use App\Infrastructure\Shopping\Model\SHP_ShoppingFavorite;
use App\Infrastructure\Shopping\Model\SHP_ShoppingSession;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ShoppingSessionRepository implements ShoppingSessionRepositoryInterface
{
    public function getById(int $id): ShoppingSession
    {
        $model = SHP_ShoppingSession::query()
            ->with(['cartLines', 'favorites', 'checkoutDraft'])
            ->find($id);

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel(SHP_ShoppingSession::class, [$id]);
        }

        return $this->mapToEntity($model);
    }

    public function findByPublicId(string $publicId): ?ShoppingSession
    {
        $model = SHP_ShoppingSession::query()
            ->with(['cartLines', 'favorites', 'checkoutDraft'])
            ->where('public_id', $publicId)
            ->first();

        return $model === null ? null : $this->mapToEntity($model);
    }

    public function findLatestByClientId(int $clientId): ?ShoppingSession
    {
        $model = SHP_ShoppingSession::query()
            ->with(['cartLines', 'favorites', 'checkoutDraft'])
            ->where('client_id', $clientId)
            ->orderByDesc('updated_at')
            ->first();

        return $model === null ? null : $this->mapToEntity($model);
    }

    public function create(string $publicId, ?int $clientId, \DateTimeImmutable $expiresAt): ShoppingSession
    {
        $model = new SHP_ShoppingSession;
        $model->public_id = $publicId;
        $model->client_id = $clientId;
        $model->expires_at = $expiresAt;
        $model->save();

        return $this->mapToEntity($model->fresh(['cartLines', 'favorites', 'checkoutDraft']));
    }

    public function save(ShoppingSession $session): void
    {
        DB::transaction(function () use ($session): void {
            /** @var SHP_ShoppingSession|null $model */
            $model = SHP_ShoppingSession::query()->find($session->getId());
            if ($model === null) {
                throw (new ModelNotFoundException)->setModel(SHP_ShoppingSession::class, [$session->getId()]);
            }

            $model->client_id = $session->getClientId();
            $model->expires_at = $session->getExpiresAt();
            $model->save();

            SHP_ShoppingCartLine::query()->where('shopping_session_id', $model->id)->delete();
            foreach ($session->getCartLines() as $line) {
                SHP_ShoppingCartLine::query()->create([
                    'shopping_session_id' => $model->id,
                    'product_id' => $line->productId,
                    'quantity' => $line->quantity,
                    'payload' => $line->payload,
                ]);
            }

            SHP_ShoppingFavorite::query()->where('shopping_session_id', $model->id)->delete();
            foreach ($session->getFavoriteProductIds() as $productId) {
                SHP_ShoppingFavorite::query()->create([
                    'shopping_session_id' => $model->id,
                    'product_id' => $productId,
                ]);
            }

            $draft = $session->getCheckoutDraft();
            if ($draft === null) {
                SHP_ShoppingCheckoutDraft::query()->where('shopping_session_id', $model->id)->delete();
            } else {
                SHP_ShoppingCheckoutDraft::query()->updateOrCreate(
                    ['shopping_session_id' => $model->id],
                    ['payload' => $draft],
                );
            }
        });
    }

    public function touchExpiresAt(int $sessionId, \DateTimeImmutable $expiresAt): void
    {
        SHP_ShoppingSession::query()
            ->whereKey($sessionId)
            ->update(['expires_at' => $expiresAt]);
    }

    public function delete(int $id): void
    {
        SHP_ShoppingSession::query()->whereKey($id)->delete();
    }

    private function mapToEntity(SHP_ShoppingSession $model): ShoppingSession
    {
        $lines = [];
        foreach ($model->cartLines as $row) {
            $lines[] = new CartLine(
                (int) $row->product_id,
                (int) $row->quantity,
                $row->payload,
            );
        }

        $favorites = $model->favorites
            ->map(static fn ($f) => (int) $f->product_id)
            ->values()
            ->all();

        $draft = $model->checkoutDraft !== null ? $model->checkoutDraft->payload : null;

        $expiresAt = $model->expires_at;
        if ($expiresAt instanceof \DateTimeInterface) {
            $expiresAt = \DateTimeImmutable::createFromInterface($expiresAt);
        } else {
            $expiresAt = (new \DateTimeImmutable)->modify('+90 days');
        }

        return new ShoppingSession(
            (int) $model->id,
            (string) $model->public_id,
            $model->client_id !== null ? (int) $model->client_id : null,
            $expiresAt,
            $lines,
            $favorites,
            $draft,
            \DateTimeImmutable::createFromInterface($model->created_at),
            \DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }
}
