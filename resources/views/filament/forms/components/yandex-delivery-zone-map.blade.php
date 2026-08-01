@php
    /** @var \App\Filament\Content\Delivery\Forms\Components\YandexDeliveryZoneMap $field */
    $editorUrl = route('filament.admin.delivery-zone-map-editor');
    $record = $getRecord();
    $initialPayload = [
        'geometry' => $getState(),
        'address' => $record?->kitchen_address,
        'kitchenLatitude' => $record?->kitchen_latitude,
        'kitchenLongitude' => $record?->kitchen_longitude,
    ];
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="deliveryZoneBridge({
            geometryStatePath: @js($getStatePath()),
            kitchenAddressPath: @js($field->getKitchenAddressStatePath()),
            kitchenLatPath: @js($field->getKitchenLatitudeStatePath()),
            kitchenLngPath: @js($field->getKitchenLongitudeStatePath()),
            initialPayload: @js($initialPayload),
        })"
        class="space-y-3"
    >
        <iframe
            x-ref="zoneIframe"
            src="{{ $editorUrl }}"
            style="display:block;width:100%;height:480px;min-height:480px;border:0;"
            class="rounded-lg border border-gray-300 dark:border-gray-700"
            title="Редактор зоны доставки"
            @load="onIframeLoad()"
        ></iframe>
        <p class="text-sm text-gray-500" x-text="statusMessage"></p>
    </div>
</x-dynamic-component>
