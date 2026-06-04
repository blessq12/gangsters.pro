<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Command\DeleteCategoryUseCase;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Support\AdminActionVisibility;
use App\Infrastructure\Category\Model\PRD_Category;
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
            ->records(fn (): Collection => PRD_Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->keyBy('id'))
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
                    ->url(fn (PRD_Category $record): string => CategoryResource::getUrl('edit', ['record' => $record->getKey()])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->action(function (PRD_Category $record): void {
                        try {
                            app(DeleteCategoryUseCase::class)->execute((int) $record->getKey());
                            Notification::make()->title('Категория удалена')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Создать категорию')
                    ->url(CategoryResource::getUrl('create'))
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()),
            ]);
    }
}
