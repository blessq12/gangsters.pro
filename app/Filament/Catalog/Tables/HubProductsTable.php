<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Command\ActivateProductUseCase;
use App\Application\Catalog\Command\ArchiveProductUseCase;
use App\Application\Catalog\Command\DeleteProductUseCase;
use App\Application\Catalog\Query\GetAdminProductListQuery;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Product\Entity\Product;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Support\AdminActionVisibility;
use App\Support\Product\ProductStatusLabels;
use App\Filament\Catalog\Resources\ProductResource\Tables\ProductsTable;
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

                $result = app(GetAdminProductListQuery::class)->execute(
                    search: filled($search) ? $search : null,
                    status: filled($status) ? (string) $status : null,
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
            ->columns(ProductsTable::listColumns())
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        ...ProductStatusLabels::options(),
                    ]),
            ])
            ->searchable()
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => ProductResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('archive')
                    ->label('В архив')
                    ->color('warning')
                    ->visible(fn (array $record): bool => AdminActionVisibility::canMutate()
                        && $record['status'] !== Product::STATUS_ARCHIVED)
                    ->action(fn (array $record) => $this->runArchiveActivate(
                        fn () => app(ArchiveProductUseCase::class)->execute((int) $record['id']),
                        'В архиве',
                    )),
                Action::make('activate')
                    ->label('Активировать')
                    ->color('success')
                    ->visible(fn (array $record): bool => AdminActionVisibility::canMutate()
                        && $record['status'] === Product::STATUS_ARCHIVED)
                    ->action(fn (array $record) => $this->runArchiveActivate(
                        fn () => app(ActivateProductUseCase::class)->execute((int) $record['id']),
                        'Активирован',
                    )),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->modalDescription('Товар будет удалён безвозвратно. Если удаление недоступно — используйте архивацию.')
                    ->action(function (array $record): void {
                        try {
                            app(DeleteProductUseCase::class)->execute((int) $record['id']);
                            Notification::make()->title('Товар удалён')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Создать товар')
                    ->url(ProductResource::getUrl('create'))
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()),
            ]);
    }

    /**
     * @param  callable(): void  $callback
     */
    private function runArchiveActivate(callable $callback, string $successTitle): void
    {
        try {
            $callback();
            Notification::make()->title($successTitle)->success()->send();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }
}
