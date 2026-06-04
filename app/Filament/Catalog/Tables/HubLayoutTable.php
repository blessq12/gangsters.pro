<?php

namespace App\Filament\Catalog\Tables;

use App\Application\Catalog\Command\SetCategoryProductsUseCase;
use App\Application\Catalog\DTO\SetCategoryProductsDTO;
use App\Filament\Support\AdminCatalogLayoutReadHelper;
use App\Application\Common\Exceptions\ApiException;
use App\Filament\Support\AdminActionVisibility;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class HubLayoutTable extends TableWidget
{
    protected static ?string $heading = 'Раскладка';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (?array $filters): Collection {
                $categoryId = $this->resolveCategoryId($filters);
                if ($categoryId === null || ! $this->categoryExists($categoryId)) {
                    return collect();
                }

                $layout = app(AdminCatalogLayoutReadHelper::class)->layoutForCategory($categoryId);

                return collect($layout['products'])->keyBy('id');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Товар'),
                TextColumn::make('articul')
                    ->label('Артикул'),
                TextColumn::make('price_rubles')
                    ->label('Цена')
                    ->formatStateUsing(fn ($state): string => $state !== null && $state !== ''
                        ? Money::formatRublesRuAdaptive((float) $state).' ₽'
                        : '—'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->options(fn (): array => app(AdminCatalogLayoutReadHelper::class)->categoryOptions())
                    ->searchable()
                    ->native(false),
            ])
            ->filtersFormColumns(1)
            ->reorderable()
            ->paginated(false)
            ->emptyStateHeading('Выберите категорию')
            ->emptyStateDescription('Укажите категорию в фильтре, чтобы редактировать порядок товаров.')
            ->headerActions([
                Action::make('addProduct')
                    ->label('Добавить товар')
                    ->form([
                        Select::make('product_id')
                            ->label('Товар')
                            ->options(fn (): array => app(AdminCatalogLayoutReadHelper::class)->productOptionsForSelect())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $this->appendProduct((int) $data['product_id']);
                    })
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()
                        && $this->selectedCategoryId() !== null),
            ])
            ->recordActions([
                Action::make('remove')
                    ->label('Убрать')
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->action(fn (array $record) => $this->removeProduct((int) $record['id'])),
            ]);
    }

    /**
     * @param  array<int | string>  $order
     */
    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        $categoryId = $this->selectedCategoryId();
        if ($categoryId === null) {
            return;
        }

        try {
            app(SetCategoryProductsUseCase::class)->execute(new SetCategoryProductsDTO(
                categoryId: $categoryId,
                productIds: array_map(intval(...), array_values($order)),
            ));
            Notification::make()->title('Порядок сохранён')->success()->send();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    /**
     * @param  ?array<string, mixed>  $filters
     */
    private function resolveCategoryId(?array $filters): ?int
    {
        $value = $filters['category_id']['value'] ?? $filters['category_id'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function selectedCategoryId(): ?int
    {
        $categoryId = $this->resolveCategoryId($this->tableFilters);
        if ($categoryId === null || ! $this->categoryExists($categoryId)) {
            return null;
        }

        return $categoryId;
    }

    private function categoryExists(int $categoryId): bool
    {
        return app(AdminCatalogLayoutReadHelper::class)->categoryExists($categoryId);
    }

    private function appendProduct(int $productId): void
    {
        $categoryId = $this->selectedCategoryId();
        if ($categoryId === null) {
            return;
        }

        $ids = $this->currentProductIds();
        if (in_array($productId, $ids, true)) {
            return;
        }

        $ids[] = $productId;
        $this->persistProductIds($categoryId, $ids);
    }

    private function removeProduct(int $productId): void
    {
        $categoryId = $this->selectedCategoryId();
        if ($categoryId === null) {
            return;
        }

        $ids = array_values(array_filter(
            $this->currentProductIds(),
            static fn (int $id): bool => $id !== $productId,
        ));

        $this->persistProductIds($categoryId, $ids);
    }

    /**
     * @return int[]
     */
    private function currentProductIds(): array
    {
        $categoryId = $this->selectedCategoryId();
        if ($categoryId === null) {
            return [];
        }

        $layout = app(AdminCatalogLayoutReadHelper::class)->layoutForCategory($categoryId);

        return array_map(
            static fn (array $row): int => (int) $row['id'],
            $layout['products'],
        );
    }

    /**
     * @param  int[]  $productIds
     */
    private function persistProductIds(int $categoryId, array $productIds): void
    {
        try {
            app(SetCategoryProductsUseCase::class)->execute(new SetCategoryProductsDTO(
                categoryId: $categoryId,
                productIds: $productIds,
            ));
            Notification::make()->title('Раскладка сохранена')->success()->send();
            $this->resetTable();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }
}
