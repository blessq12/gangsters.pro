<?php

namespace App\Filament\Marketing\Resources\BannerResource\Pages;

use App\Application\Marketing\Banner\Command\SaveBannerUseCase;
use App\Filament\Marketing\Resources\BannerResource;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $saved = app(SaveBannerUseCase::class)->execute($data);

        return SYS_Banner::query()->findOrFail($saved['id']);
    }
}
