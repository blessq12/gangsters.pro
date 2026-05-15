@php
    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();
    $apiKey = $field->getMapsApiKey();
    $record = $field->getRecord();
    $editorUrl = $field->getEditorUrl($record);
    $kitchenAddress = $field->getKitchenAddressLine();
@endphp

@once
    @push('styles')
        <style>
            .fi-fo-yandex-delivery-zone-map {
                width: 100%;
                max-width: none;
            }
            .fi-fo-yandex-delivery-zone-map .delivery-zone-map-shell {
                width: 100%;
                max-width: none;
            }
            .fi-fo-yandex-delivery-zone-map .delivery-zone-map-iframe {
                display: block;
                width: 100%;
                max-width: none;
                min-height: 500px;
                height: min(70vh, 720px);
            }
            .fi-fo-yandex-delivery-zone-map .delivery-zone-field-error {
                margin-top: 0.25rem;
                font-size: 0.875rem;
                line-height: 1.25rem;
                color: rgb(220 38 38);
            }
            .dark .fi-fo-yandex-delivery-zone-map .delivery-zone-field-error {
                color: rgb(248 113 113);
            }
        </style>
    @endpush
@endonce

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    class="fi-fo-yandex-delivery-zone-map w-full max-w-none"
>
    @if ($apiKey === null)
        <div class="rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
            Укажите <code class="text-xs">YANDEX_MAPS_API_KEY</code> в <code class="text-xs">.env</code>, чтобы открыть редактор зоны доставки.
        </div>
    @elseif ($editorUrl === null)
        <div class="rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
            Сохраните запись компании, затем откройте редактор зоны.
        </div>
    @else
        <div
            x-data="deliveryZoneBridge"
            x-init="
                geometryStatePath = @js($statePath);
                kitchenAddress = @js($kitchenAddress);
                kitchenLatPath = 'data.kitchen_latitude';
                kitchenLngPath = 'data.kitchen_longitude';
            "
            class="delivery-zone-map-shell w-full max-w-none space-y-2"
        >
            <iframe
                wire:ignore
                x-ref="zoneIframe"
                src="{{ $editorUrl }}"
                title="Редактор зоны бесплатной доставки"
                class="delivery-zone-map-iframe rounded-lg border border-gray-300 dark:border-gray-600"
                @load="onIframeLoad()"
            ></iframe>
            <p
                class="text-xs text-gray-500 dark:text-gray-400"
                x-show="statusMessage"
                x-text="statusMessage"
            ></p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Внутри зоны на карте — бесплатная доставка. «Применить» в редакторе, затем «Сохранить» внизу формы.
            </p>
        </div>

        @if ($errors->has($statePath))
            <p class="delivery-zone-field-error" role="alert">
                {{ $errors->first($statePath) }}
            </p>
        @endif

        @once
            @push('scripts')
                <script src="{{ asset('js/filament/delivery-zone-iframe-bridge.js') }}?v=7"></script>
            @endpush
        @endonce
    @endif
</x-dynamic-component>
