<?php

namespace App\Filament\Operations\Tables;

use App\Application\Catalog\DTO\UpdateCartRuleFlagsDTO;
use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\CartRules\Contracts\UpdateProductCartRuleFlagsContract;
use App\Application\Operations\CartRules\Query\GetAdminCartRuleProductsQuery;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Operations\Concerns\ConfiguresHubTablePagination;
use App\Support\Product\ProductStatusLabels;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;

class HubCartRulesProductsTable extends TableWidget
{
    use ConfiguresHubTablePagination;

    protected static ?string $heading = 'Товары с флагами правил корзины';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $table = $table
            ->records(function (
                ?string $search,
                ?array $filters,
                int|string $page,
                int|string $recordsPerPage,
            ): LengthAwarePaginator {
                $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 25;
                $status = $filters['status']['value'] ?? null;

                $result = app(GetAdminCartRuleProductsQuery::class)->execute(
                    search: filled($search) ? $search : null,
                    status: filled($status) ? (string) $status : null,
                    page: max(1, (int) $page),
                    perPage: $perPage,
                    countsAsRoll: $this->filterBool($filters, 'counts_as_roll'),
                    giftCandidate: $this->filterBool($filters, 'gift_candidate'),
                    isComplementSet: $this->filterBool($filters, 'is_complement_set'),
                );

                return $this->buildHubLengthAwarePaginator(
                    $result,
                    max(1, (int) $page),
                    $perPage,
                );
            })
            ->columns([
                TextColumn::make('name')->label('Название'),
                TextColumn::make('articul')->label('Артикул'),
                IconColumn::make('counts_as_roll')->label('Ролл')->boolean(),
                IconColumn::make('gift_candidate')->label('Подарок')->boolean(),
                IconColumn::make('is_complement_set')->label('Комплект')->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ProductStatusLabels::options()),
                TernaryFilter::make('counts_as_roll')->label('Считается роллом'),
                TernaryFilter::make('gift_candidate')->label('Кандидат в подарок'),
                TernaryFilter::make('is_complement_set')->label('Комплект'),
            ])
            ->searchable()
            ->recordActions([
                EditAction::make()
                    ->url(fn (array $record): string => ProductResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('toggle_roll')
                    ->label('Ролл')
                    ->action(fn (array $record) => $this->toggleFlag($record, 'counts_as_roll')),
                Action::make('toggle_gift')
                    ->label('Подарок')
                    ->action(fn (array $record) => $this->toggleFlag($record, 'gift_candidate')),
                Action::make('toggle_complement')
                    ->label('Комплект')
                    ->action(fn (array $record) => $this->toggleFlag($record, 'is_complement_set')),
            ]);

        return $this->configureHubPagination($table, 'cartRulesProducts');
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    private function filterBool(?array $filters, string $key): ?bool
    {
        if ($filters === null || ! isset($filters[$key]['value'])) {
            return null;
        }

        $value = $filters[$key]['value'];

        if ($value === null || $value === '') {
            return null;
        }

        return (bool) $value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function toggleFlag(array $record, string $field): void
    {
        $flags = [
            'counts_as_roll' => (bool) ($record['counts_as_roll'] ?? false),
            'gift_candidate' => (bool) ($record['gift_candidate'] ?? false),
            'is_complement_set' => (bool) ($record['is_complement_set'] ?? false),
        ];

        $flags[$field] = ! $flags[$field];

        try {
            app(UpdateProductCartRuleFlagsContract::class)->execute(new UpdateCartRuleFlagsDTO(
                productId: (int) $record['id'],
                countsAsRoll: $flags['counts_as_roll'],
                giftCandidate: $flags['gift_candidate'],
                isComplementSet: $flags['is_complement_set'],
            ));
            Notification::make()->title('Флаги обновлены')->success()->send();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }
}
