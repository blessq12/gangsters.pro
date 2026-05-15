<?php

namespace App\Filament\Forms\Components;

use App\Application\SystemContent\Support\CompanyKitchenAddressFormatter;
use App\Domain\SystemContent\ValueObject\DeliveryZoneGeometry;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class YandexDeliveryZoneMap extends Field
{
    /**
     * @var view-string
     */
    protected string $view = 'filament.forms.components.yandex-delivery-zone-map';

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull();

        $this->dehydrated(true);

        $this->extraFieldWrapperAttributes([
            'class' => 'w-full max-w-none [&_.fi-fo-field-wrp]:w-full',
        ]);

        $this->dehydrateStateUsing(static function (mixed $state): ?array {
            if ($state === null || $state === '' || $state === []) {
                return null;
            }

            return DeliveryZoneGeometry::fromMixed($state)?->toStorage();
        });

        $this->rules([
            fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                if ($value === null || $value === '' || $value === []) {
                    return;
                }

                try {
                    DeliveryZoneGeometry::fromMixed($value);
                } catch (InvalidArgumentException $exception) {
                    $fail($exception->getMessage());
                }
            },
        ]);
    }

    public function getKitchenAddressLine(): string
    {
        $record = $this->getRecord();
        if ($record === null) {
            return '';
        }

        return CompanyKitchenAddressFormatter::format($record);
    }

    public function getMapsApiKey(): ?string
    {
        $key = config('services.yandex_maps.api_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function getEditorUrl(?Model $record): ?string
    {
        if ($record === null || ! $record->exists) {
            return null;
        }

        $panel = Filament::getPanel('admin');

        return $panel->route('delivery-zone-map.editor', [
            'company' => $record->getKey(),
        ]);
    }
}
