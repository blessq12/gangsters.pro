<?php

namespace App\Filament\Marketing\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Promotion\Command\DeletePromotionUseCase;
use App\Application\Marketing\Promotion\Query\GetAdminPromotionListQuery;
use App\Filament\Marketing\Resources\PromotionResource;
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

class HubPromotionsTable extends TableWidget
{
    protected static ?string $heading = 'Акции';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): LengthAwarePaginator {
                $items = app(GetAdminPromotionListQuery::class)->execute();

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
                    ->label('Заголовок'),
                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(PromotionResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => PromotionResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        try {
                            app(DeletePromotionUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Акция удалена')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}
