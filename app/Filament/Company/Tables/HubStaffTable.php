<?php

namespace App\Filament\Company\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Staff\Command\DeleteAdminUserUseCase;
use App\Filament\Company\Resources\StaffUserResource;
use App\Filament\Support\AdminActionVisibility;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use App\Domain\Admin\Enums\AdminRole;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
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
                $query = User::query()
                    ->whereNotNull('admin_role')
                    ->orderByDesc('id');

                if (filled($search)) {
                    $query->where(function (Builder $builder) use ($search): void {
                        $builder
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('tel', 'like', '%'.$search.'%');
                    });
                }

                $paginator = $query->paginate(perPage: $perPage, page: max(1, (int) $page));

                return new LengthAwarePaginator(
                    collect($paginator->items())->keyBy('id'),
                    $paginator->total(),
                    $perPage,
                    max(1, (int) $page),
                    ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
                );
            })
            ->columns([
                TextColumn::make('name')->label('Имя'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('tel')->label('Телефон'),
                TextColumn::make('admin_role')
                    ->label('Роль')
                    ->formatStateUsing(fn (?AdminRole $state): string => $state?->label() ?? '—'),
            ])
            ->searchable()
            ->headerActions([
                CreateAction::make()
                    ->url(StaffUserResource::getUrl('create'))
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (User $record): string => StaffUserResource::getUrl('edit', ['record' => $record->getKey()])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        try {
                            app(DeleteAdminUserUseCase::class)->execute((int) $record->getKey());
                            Notification::make()->title('Сотрудник удалён')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}
