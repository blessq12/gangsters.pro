<?php

namespace App\Filament\Marketing\Tables;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Promotion\Command\DeletePromotionUseCase;
use App\Filament\Marketing\Resources\PromotionResource;
use App\Filament\Support\AdminActionVisibility;
use App\Filament\Support\ResolvesAdminBannerImageUrl;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class HubPromotionsTable extends TableWidget
{
    use ResolvesAdminBannerImageUrl;

    protected static ?string $heading = 'Акции';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => SYS_Promotion::query()
                ->orderByDesc('id')
                ->get()
                ->keyBy('id'))
            ->columns([
                ImageColumn::make('image_url')
                    ->label('')
                    ->height(48)
                    ->width(80)
                    ->getStateUsing(fn (SYS_Promotion $record): ?string => $this->resolveBannerPreviewUrl($record)),
                TextColumn::make('title')
                    ->label('Заголовок'),
                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->paginated(false)
            ->headerActions([
                CreateAction::make()
                    ->url(PromotionResource::getUrl('create'))
                    ->visible(fn (): bool => AdminActionVisibility::canMutate()),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (SYS_Promotion $record): string => PromotionResource::getUrl('edit', ['record' => $record->getKey()])),
                Action::make('delete')
                    ->label('Удалить')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => AdminActionVisibility::canMutate())
                    ->requiresConfirmation()
                    ->action(function (SYS_Promotion $record): void {
                        try {
                            app(DeletePromotionUseCase::class)->execute((int) $record->getKey());
                            Notification::make()->title('Акция удалена')->success()->send();
                        } catch (ApiException $exception) {
                            Notification::make()->title($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }
}
