<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Command\ActivateProductUseCase;
use App\Application\Catalog\Command\ArchiveProductUseCase;
use App\Application\Catalog\Command\DeleteProductUseCase;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Resources\ProductResource\Tables\ProductsTable;
use App\Filament\Support\AdminActionVisibility;
use App\Filament\Support\AdminProductTableQuery;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubProductsTable extends TableWidget
{
    protected static ?string $heading = 'Товары';

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
                $status = $filters['status']['value'] ?? null;
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;

                return app(AdminProductTableQuery::class)->paginate(
                    search: filled($search) ? $search : null,
                    status: filled($status) ? (string) $status : null,
                    page: max(1, (int) $page),
                    perPage: $perPage,
                    pageName: $this->getTablePaginationPageName(),
                )->onEachSide(0);
            })
            ->columns(ProductsTable::listColumns())
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(\App\Support\Product\ProductStatusLabels::options()),
            ])
            ->searchable()
            ->headerActions([
                CreateAction::make()
                    ->label('Создать товар')
                    ->url(ProductResource::getUrl('create'))
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => ProductResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('activate')
                    ->label('Активировать')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (array $record): bool => AdminActionVisibility::canMutate()
                        && ($record['status'] ?? '') !== \App\Domain\Product\Entity\Product::STATUS_ACTIVE)
                    ->action(function (array $record): void {
                        try {
                            app(ActivateProductUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Товар активирован')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('archive')
                    ->label('В архив')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('warning')
                    ->visible(fn (array $record): bool => AdminActionVisibility::canMutate()
                        && ($record['status'] ?? '') === \App\Domain\Product\Entity\Product::STATUS_ACTIVE)
                    ->action(function (array $record): void {
                        try {
                            app(ArchiveProductUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Товар в архиве')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        try {
                            app(DeleteProductUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Товар удалён')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}
