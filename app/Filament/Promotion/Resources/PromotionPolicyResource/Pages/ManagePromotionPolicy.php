<?php

namespace App\Filament\Promotion\Resources\PromotionPolicyResource\Pages;

use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Filament\Promotion\Resources\PromotionPolicyResource;
use App\Filament\Promotion\Resources\PromotionPolicyResource\Schemas\PromotionPolicyForm;
use App\Infrastructure\Promotion\Model\PRM_Configuration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class ManagePromotionPolicy extends EditRecord
{
    protected static string $resource = PromotionPolicyResource::class;

    protected static ?string $title = 'Правила акций';

    protected static ?string $navigationLabel = 'Правила акций';

    public function mount(int|string $record = PromotionPolicyRepository::SINGLETON_ID): void
    {
        PRM_Configuration::query()->firstOrCreate(
            ['id' => PromotionPolicyRepository::SINGLETON_ID],
            [
                'gift_pickup_min_order_kopecks' => 100_000,
                'gift_courier_min_order_kopecks' => 180_000,
                'gift_benefit_active' => true,
                'delivery_free_threshold_kopecks' => 100_000,
                'delivery_outside_zone_surcharge_kopecks' => 20_000,
                'delivery_benefit_active' => true,
            ],
        );

        parent::mount($record);
    }

    public function form(Schema $schema): Schema
    {
        return PromotionPolicyForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save'),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Правила акций сохранены');
    }
}
