<?php

namespace App\Filament\Company\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Content\Document\Command\DeleteDocumentUseCase;
use App\Filament\Company\Resources\DocumentResource;
use App\Filament\Support\AdminActionVisibility;
use App\Infrastructure\SystemContent\Model\SYS_Document;
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

class HubDocumentsTable extends TableWidget
{
    protected static ?string $heading = 'Документы';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => SYS_Document::query()
                ->orderBy('key')
                ->get()
                ->keyBy('id'))
            ->columns([
                TextColumn::make('key')->label('Ключ'),
                TextColumn::make('title')->label('Заголовок'),
                IconColumn::make('is_active')->label('Активен')->boolean(),
            ])
            ->paginated(false)
            ->headerActions([
                CreateAction::make()
                    ->url(DocumentResource::getUrl('create'))
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (SYS_Document $record): string => DocumentResource::getUrl('edit', ['record' => $record->getKey()])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->modalDescription(fn (SYS_Document $record): string => 'Документ «'.($record->title ?? '—').'» будет удалён безвозвратно.')
                    ->action(function (SYS_Document $record): void {
                        try {
                            app(DeleteDocumentUseCase::class)->execute((int) $record->getKey());
                            Notification::make()->title('Документ удалён')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}
