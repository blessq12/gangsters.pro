<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Command\DeleteTagUseCase;
use App\Application\Catalog\Query\ListAdminTagsQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\TagResource;
use App\Filament\Support\AdminActionVisibility;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class HubTagsTable extends TableWidget
{
    protected static ?string $heading = 'Теги';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): Collection {
                $items = app(ListAdminTagsQuery::class)->execute();

                return collect($items)->keyBy('id');
            })
            ->columns([
                TextColumn::make('label')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Код'),
                TextColumn::make('color')
                    ->label('Цвет'),
                TextColumn::make('sort_order')
                    ->label('Порядок'),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->searchable()
            ->paginated(false)
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => TagResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        try {
                            app(DeleteTagUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Тег удалён')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Создать тег')
                    ->url(TagResource::getUrl('create'))
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()),
            ]);
    }
}
