<?php

namespace App\Filament\Marketing\Resources\PromotionResource\Pages;

use App\Application\Marketing\Promotion\Command\SavePromotionUseCase;
use App\Filament\Marketing\Resources\PromotionResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPromotion extends EditRecord
{
    protected static string $resource = PromotionResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['id'] = (int) $record->getKey();
        app(SavePromotionUseCase::class)->execute($data);

        return $record->refresh();
    }
}
