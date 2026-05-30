@php
    $editorUrl = route('filament.admin.delivery-zone-map-editor');
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="deliveryZoneBridge" class="space-y-3">
        @once
            @push('scripts')
                <script src="{{ asset('js/filament/delivery-zone-iframe-bridge.js') }}"></script>
            @endpush
        @endonce
        <iframe
            x-ref="zoneIframe"
            src="{{ $editorUrl }}"
            class="h-[480px] w-full rounded-lg border border-gray-300 dark:border-gray-700"
            title="Редактор зоны доставки"
            @load="onIframeLoad()"
        ></iframe>
        <p class="text-sm text-gray-500" x-text="statusMessage"></p>
    </div>
</x-dynamic-component>
