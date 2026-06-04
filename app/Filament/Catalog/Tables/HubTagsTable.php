<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Command\DeleteTagUseCase;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\TagResource;
use App\Filament\Support\AdminActionVisibility;
use App\Infrastructure\Product\Model\PRD_Tag;
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
            ->records(fn (): Collection => PRD_Tag::query()
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get()
                ->keyBy('id'))
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
                    ->url(fn (PRD_Tag $record): string => TagResource::getUrl('edit', ['record' => $record->getKey()])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->action(function (PRD_Tag $record): void {
                        try {
                            app(DeleteTagUseCase::class)->execute((int) $record->getKey());
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
