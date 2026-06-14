<?php

namespace App\Filament\Catalog\Resources\CategoryResource\RelationManagers;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Infrastructure\Catalog\Model\PRD_CategoryProduct;
use App\Infrastructure\Catalog\Model\PRD_Product;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class CategoryProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'categoryProducts';

    protected static ?string $title = 'Состав';

    protected static string | \BackedEnum | null $icon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldSkipAuthorization = true;

    protected static bool $shouldCheckPolicyExistence = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('product_id')
                    ->label('Товар или набор')
                    ->options(function (): array {
                        $assignedIds = $this->getOwnerRecord()
                            ->categoryProducts()
                            ->pluck('product_id');

                        return PRD_Product::query()
                            ->whereNotIn('id', $assignedIds)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(
                                fn (PRD_Product $product): array => [
                                    $product->getKey() => sprintf(
                                        '%s (%s)',
                                        $product->name,
                                        $product->catalog_kind === CatalogItemKind::Set->value ? 'набор' : 'товар',
                                    ),
                                ],
                            )
                            ->all();
                    })
                    ->searchable()
                    ->required()
                    ->rules([
                        Rule::unique(PRD_CategoryProduct::class, 'product_id')
                            ->where(fn ($query) => $query->where(
                                'category_id',
                                $this->getOwnerRecord()->getKey(),
                            )),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with('product'),
            )
            ->modelLabel('Позиция')
            ->pluralModelLabel('Позиции')
            ->emptyStateHeading('В категории пока нет позиций')
            ->emptyStateDescription('Добавьте товар или набор в категорию.')
            ->heading('Состав категории')
            ->description('Перетаскивайте строки, чтобы изменить порядок в каталоге.')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.catalog_kind')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => $state === CatalogItemKind::Set->value ? 'Набор' : 'Товар',
                    ),
                TextColumn::make('product.price')
                    ->label('Цена, ₽')
                    ->formatStateUsing(
                        fn (?int $state): string => $state === null ? '—' : number_format($state, 0, ',', ' ').' ₽',
                    )
                    ->sortable(),
                TextColumn::make('product.status')
                    ->label('Статус')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить позицию'),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Убрать из категории'),
            ])
            ->selectable(false);
    }

    protected function canReorder(): bool
    {
        return true;
    }
}
