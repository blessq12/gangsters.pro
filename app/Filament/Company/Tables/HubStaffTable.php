<?php

namespace App\Filament\Company\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Staff\Command\DeleteAdminUserUseCase;
use App\Application\Company\Staff\Query\GetAdminStaffListQuery;
use App\Filament\Company\Resources\StaffUserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubStaffTable extends TableWidget
{
    protected static ?string $heading = 'Сотрудники';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (
                ?string $search,
                ?array $filters,
                int|string $page,
                int|string $recordsPerPage,
            ): LengthAwarePaginator {
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;

                $result = app(GetAdminStaffListQuery::class)->execute(
                    search: filled($search) ? $search : null,
                    page: max(1, (int) $page),
                    perPage: $perPage,
                );

                return new LengthAwarePaginator(
                    collect($result['items'])->keyBy('id'),
                    $result['total'],
                    $perPage,
                    max(1, (int) $page),
                    ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
                );
            })
            ->columns([
                TextColumn::make('name')->label('Имя'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('tel')->label('Телефон'),
            ])
            ->searchable()
            ->headerActions([
                CreateAction::make()
                    ->url(StaffUserResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => StaffUserResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription(fn (array $record): string => 'Сотрудник «'.($record['name'] ?? '—').'» будет удалён безвозвратно.')
                    ->action(function (array $record): void {
                        try {
                            app(DeleteAdminUserUseCase::class)->execute(
                                (int) $record['id'],
                                (int) auth()->id(),
                            );
                            Notification::make()->title('Сотрудник удалён')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}
