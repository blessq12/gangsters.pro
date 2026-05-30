<?php

namespace App\Filament\Marketing\Resources\PromotionResource\Pages;

use App\Application\Marketing\Promotion\Command\SavePromotionUseCase;
use App\Filament\Marketing\Resources\PromotionResource;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePromotion extends CreateRecord
{
    protected static string $resource = PromotionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $saved = app(SavePromotionUseCase::class)->execute($data);

        return SYS_Promotion::query()->findOrFail($saved['id']);
    }
}
