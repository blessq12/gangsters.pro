<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Command\DeleteCategoryUseCase;
use App\Application\Catalog\Query\GetAdminCategoryListQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\CategoryResource;
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

class HubCategoriesTable extends TableWidget
{
    protected static ?string $heading = 'Категории';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): Collection {
                $items = app(GetAdminCategoryListQuery::class)->execute();

                return collect($items)->keyBy('id');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug'),
                TextColumn::make('sort_order')
                    ->label('Порядок'),
                IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
            ])
            ->searchable()
            ->paginated(false)
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => CategoryResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        try {
                            app(DeleteCategoryUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Категория удалена')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Создать категорию')
                    ->url(CategoryResource::getUrl('create')),
            ]);
    }
}
