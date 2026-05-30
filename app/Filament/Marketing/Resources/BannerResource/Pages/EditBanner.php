<?php

namespace App\Filament\Marketing\Resources\BannerResource\Pages;

use App\Application\Marketing\Banner\Command\SaveBannerUseCase;
use App\Filament\Marketing\Resources\BannerResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['id'] = (int) $record->getKey();
        app(SaveBannerUseCase::class)->execute($data);

        return $record->refresh();
    }
}
