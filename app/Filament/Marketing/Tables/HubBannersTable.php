<?php

namespace App\Filament\Marketing\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Banner\Command\DeleteBannerUseCase;
use App\Application\Marketing\Banner\Query\GetAdminBannerListQuery;
use App\Filament\Marketing\Resources\BannerResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubBannersTable extends TableWidget
{
    protected static ?string $heading = 'Баннеры';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $items = app(GetAdminBannerListQuery::class)->execute();

                return new LengthAwarePaginator(
                    collect($items)->keyBy('id'),
                    count($items),
                    max(count($items), 1),
                    1,
                    ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
                );
            })
            ->columns([
                ImageColumn::make('image_url')
                    ->label('')
                    ->height(48)
                    ->width(80),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->placeholder('—'),
                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(BannerResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => BannerResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        try {
                            app(DeleteBannerUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Баннер удалён')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}
