<?php

namespace App\Filament\Catalog\Resources\ProductSetResource\RelationManagers;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Infrastructure\Catalog\Model\PRD_ProductSetLine;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class ProductSetLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'setLines';

    protected static ?string $title = 'Состав';

    protected static string | \BackedEnum | null $icon = Heroicon::OutlinedListBullet;

    protected static bool $shouldSkipAuthorization = true;

    protected static bool $shouldCheckPolicyExistence = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('product_id')
                    ->label('Товар')
                    ->options(function (): array {
                        $assignedIds = $this->getOwnerRecord()
                            ->setLines()
                            ->pluck('product_id');

                        return PRD_Product::query()
                            ->where('catalog_kind', CatalogItemKind::Product->value)
                            ->whereNotIn('id', $assignedIds)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->required()
                    ->hiddenOn('edit')
                    ->rules([
                        Rule::unique(PRD_ProductSetLine::class, 'product_id')
                            ->where(fn ($query) => $query->where(
                                'set_id',
                                $this->getOwnerRecord()->getKey(),
                            )),
                    ]),
                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
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
            ->emptyStateHeading('В наборе пока нет товаров')
            ->emptyStateDescription('Добавьте товар в состав набора.')
            ->heading('Состав набора')
            ->description('Перетаскивайте строки, чтобы изменить порядок товаров в наборе.')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Товар')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.price')
                    ->label('Цена, ₽')
                    ->formatStateUsing(
                        fn (?int $state): string => $state === null ? '—' : number_format($state, 0, ',', ' ').' ₽',
                    )
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить товар'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Изменить количество'),
                DeleteAction::make()
                    ->label('Убрать из набора'),
            ])
            ->selectable(false);
    }

    protected function canReorder(): bool
    {
        return true;
    }
}
