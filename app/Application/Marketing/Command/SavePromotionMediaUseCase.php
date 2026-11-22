<?php

namespace App\Application\Marketing\Command;

use App\Application\Marketing\DTO\SavePromotionMediaDto;
use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Supporting\Promotion\Repository\CampaignRepository;
use App\Infrastructure\Marketing\Support\MarketingMediaStore;
use App\Infrastructure\Marketing\Support\MarketingStoredMedia;
use Illuminate\Support\Facades\DB;

final class SavePromotionMediaUseCase
{
    public function __construct(
        private readonly CampaignRepository $promotions,
        private readonly MarketingPresenter $presenter,
        private readonly MarketingMediaStore $media,
    ) {}

    /**
     * @return array{promotion: array<string, mixed>, created: bool}
     */
    public function execute(SavePromotionMediaDto $dto): array
    {
        $title = trim($dto->title);
        if ($title === '') {
            throw new \InvalidArgumentException('Заголовок обязателен.');
        }

        $body = trim((string) ($dto->body ?? ''));
        $body = $body !== '' ? $body : null;
        $isCreate = $dto->id === null;
        $storedPaths = [];

        try {
            $promotion = DB::transaction(function () use ($dto, $title, $body, $isCreate, &$storedPaths) {
                $previousPath = $dto->id !== null
                    ? $this->promotions->findStoredImagePath($dto->id)
                    : null;

                $imagePath = null;
                if ($dto->removeImage) {
                    $imagePath = '';
                }

                $promotion = $this->promotions->save(
                    id: $dto->id,
                    title: $title,
                    body: $body,
                    isActive: $dto->isActive,
                    imagePath: $imagePath,
                );

                if ($dto->imageFile !== null) {
                    $imagePath = $this->media->storePromotionImage($promotion->id(), $dto->imageFile);
                    $storedPaths[] = $imagePath;
                    $promotion = $this->promotions->save(
                        id: $promotion->id(),
                        title: $title,
                        body: $body,
                        isActive: $dto->isActive,
                        imagePath: $imagePath,
                    );
                    MarketingStoredMedia::deleteIfStored($previousPath);
                } elseif ($dto->removeImage) {
                    MarketingStoredMedia::deleteIfStored($previousPath);
                }

                return $promotion;
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                MarketingStoredMedia::deleteIfStored($path);
            }
            throw $exception;
        }

        return [
            'promotion' => $this->presenter->promotion($promotion),
            'created' => $isCreate,
        ];
    }
}
