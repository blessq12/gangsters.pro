@php
    /** @var \App\Filament\Content\Delivery\Forms\Components\YandexDeliveryZoneMap $field */
    // Relative URL: same origin as admin (APP_URL mismatch breaks postMessage).
    $editorUrl = route('filament.admin.delivery-zone-map-editor', [], false);
    $record = $getRecord();
    $initialPayload = [
        'zones' => $getState() ?? [],
        'kitchenLatitude' => $record?->kitchen_latitude,
        'kitchenLongitude' => $record?->kitchen_longitude,
    ];
    $zonesStatePath = $getStatePath();
    $kitchenAddressPath = $field->getKitchenAddressStatePath();
    $kitchenLatPath = $field->getKitchenLatitudeStatePath();
    $kitchenLngPath = $field->getKitchenLongitudeStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{--
      Вся логика моста инлайн: на shared hosting только PHP, без Vite.
      Внешний Filament asset легко 404/не деплоится → Alpine.data нет → save бесполезен.
    --}}
    <div
        wire:ignore
        x-data="deliveryZoneBridgeInline({
            zonesStatePath: @js($zonesStatePath),
            kitchenAddressPath: @js($kitchenAddressPath),
            kitchenLatPath: @js($kitchenLatPath),
            kitchenLngPath: @js($kitchenLngPath),
            initialPayload: @js($initialPayload),
        })"
        class="space-y-3"
    >
        <iframe
            x-ref="zoneIframe"
            src="{{ $editorUrl }}"
            style="display:block;width:100%;height:720px;min-height:720px;border:0;"
            class="rounded-lg border border-gray-300 dark:border-gray-700"
            title="Редактор зон доставки"
            @load="onIframeLoad()"
        ></iframe>
        <p class="text-sm text-gray-500" x-text="statusMessage"></p>
    </div>
</x-dynamic-component>

@script
    <script>
        const MSG = {
            READY: 'delivery-zone:ready',
            INIT: 'delivery-zone:init',
            CHANGE: 'delivery-zone:change',
            REQUEST_SNAPSHOT: 'delivery-zone:request-snapshot',
            SNAPSHOT: 'delivery-zone:snapshot',
            KITCHEN: 'delivery-zone:kitchen',
        };

        function cloneForPostMessage(value) {
            if (value === undefined || value === null) {
                return null;
            }
            if (
                typeof value === 'string' ||
                typeof value === 'number' ||
                typeof value === 'boolean'
            ) {
                return value;
            }
            try {
                return JSON.parse(JSON.stringify(value));
            } catch (e) {
                return null;
            }
        }

        function cloneMessagePayload(payload) {
            const cloned = cloneForPostMessage(payload);
            return cloned && typeof cloned === 'object' ? cloned : {};
        }

        Alpine.data('deliveryZoneBridgeInline', (config = {}) => ({
            state: null,
            initialPayload: config.initialPayload ?? null,
            zonesStatePath: config.zonesStatePath ?? 'data.delivery_zones',
            kitchenAddressPath: config.kitchenAddressPath ?? 'data.kitchen_address',
            kitchenLatPath: config.kitchenLatPath ?? 'data.kitchen_latitude',
            kitchenLngPath: config.kitchenLngPath ?? 'data.kitchen_longitude',
            iframeReady: false,
            iframeInitialized: false,
            statusMessage: '',
            pendingSnapshotResolver: null,
            _onWindowMessage: null,

            init() {
                window.__activeDeliveryZoneBridge = this;

                if (Array.isArray(this.initialPayload?.zones)) {
                    this.state = cloneForPostMessage(this.initialPayload.zones) || [];
                } else {
                    this.state = [];
                }

                this._onWindowMessage = (event) => this.onMessage(event);
                window.addEventListener('message', this._onWindowMessage);

                this.$nextTick(() => {
                    this.observeIframeVisibility();
                    this.pushInitToIframe();
                });
            },

            destroy() {
                if (this._onWindowMessage) {
                    window.removeEventListener('message', this._onWindowMessage);
                }
                if (window.__activeDeliveryZoneBridge === this) {
                    window.__activeDeliveryZoneBridge = null;
                }
            },

            observeIframeVisibility() {
                const iframe = this.$refs?.zoneIframe;
                if (!iframe || !('IntersectionObserver' in window)) {
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    if (
                        !this.iframeInitialized &&
                        entries.some((entry) => entry.isIntersecting)
                    ) {
                        this.pushInitToIframe();
                    }
                });

                observer.observe(iframe);
            },

            readWireValue(path) {
                if (this.$wire?.get) {
                    const value = this.$wire.get(path);
                    if (value !== undefined) {
                        return value;
                    }
                }

                if (!path.startsWith('data.')) {
                    return null;
                }

                const key = path.slice(5);
                return this.$wire?.data?.[key] ?? null;
            },

            resolveZones() {
                const fromWire = this.readWireValue(this.zonesStatePath);
                if (Array.isArray(fromWire)) {
                    return cloneForPostMessage(fromWire) || [];
                }
                if (Array.isArray(this.state)) {
                    return cloneForPostMessage(this.state) || [];
                }
                if (Array.isArray(this.initialPayload?.zones)) {
                    return cloneForPostMessage(this.initialPayload.zones) || [];
                }
                return [];
            },

            buildInitPayload() {
                return {
                    zones: this.resolveZones(),
                    kitchenLatitude: cloneForPostMessage(
                        this.readWireValue(this.kitchenLatPath) ??
                            this.initialPayload?.kitchenLatitude ??
                            null,
                    ),
                    kitchenLongitude: cloneForPostMessage(
                        this.readWireValue(this.kitchenLngPath) ??
                            this.initialPayload?.kitchenLongitude ??
                            null,
                    ),
                };
            },

            pushInitToIframe() {
                const iframe = this.$refs?.zoneIframe;
                if (!iframe?.contentWindow) {
                    return false;
                }
                this.postToIframe(MSG.INIT, this.buildInitPayload());
                return true;
            },

            pushKitchenFromWire() {
                const lat = cloneForPostMessage(
                    this.readWireValue(this.kitchenLatPath),
                );
                const lng = cloneForPostMessage(
                    this.readWireValue(this.kitchenLngPath),
                );
                this.postToIframe(MSG.KITCHEN, {
                    kitchenLatitude: lat,
                    kitchenLongitude: lng,
                });
                this.statusMessage =
                    lat != null && lng != null
                        ? 'Точка кухни на карте обновлена.'
                        : 'Координаты кухни не получены — проверьте адрес и ключ геокодера.';
            },

            markIframeReady() {
                this.iframeReady = true;
                if (!this.iframeInitialized) {
                    this.pushInitToIframe();
                    this.iframeInitialized = true;
                }
            },

            postToIframe(type, payload = {}) {
                const iframe = this.$refs?.zoneIframe;
                if (!iframe?.contentWindow) {
                    return;
                }
                iframe.contentWindow.postMessage(
                    {
                        type,
                        payload: cloneMessagePayload(payload),
                    },
                    window.location.origin,
                );
            },

            onIframeLoad() {
                this.iframeInitialized = false;
                this.iframeReady = false;
                this.pushInitToIframe();
            },

            async applyPayloadToWire(payload) {
                const zones = Array.isArray(payload?.zones) ? payload.zones : [];
                return await this.applyZonesToWire(zones);
            },

            async applyZonesToWire(zones) {
                if (!this.$wire) {
                    this.statusMessage = 'Нет связи с формой. Обновите страницу.';
                    return false;
                }

                const plainZones = cloneForPostMessage(zones) || [];
                this.state = plainZones;

                try {
                    if (typeof this.$wire.syncDeliveryZones === 'function') {
                        await this.$wire.syncDeliveryZones(plainZones);
                        return true;
                    }

                    if (typeof this.$wire.set === 'function') {
                        await this.$wire.set(this.zonesStatePath, plainZones, false);
                        return true;
                    }

                    if (typeof this.$wire.$set === 'function') {
                        await this.$wire.$set(this.zonesStatePath, plainZones, false);
                        return true;
                    }
                } catch (e) {
                    this.statusMessage =
                        'Не удалось записать зоны в форму. Попробуйте ещё раз.';
                    return false;
                }

                this.statusMessage = 'Нет связи с формой. Обновите страницу.';
                return false;
            },

            syncFromIframe(timeoutMs = 4000) {
                const iframe = this.$refs?.zoneIframe;
                if (!iframe?.contentWindow) {
                    return Promise.resolve(false);
                }

                return new Promise((resolve) => {
                    const timeout = window.setTimeout(() => {
                        this.pendingSnapshotResolver = null;
                        resolve(false);
                    }, timeoutMs);

                    this.pendingSnapshotResolver = (applied) => {
                        window.clearTimeout(timeout);
                        this.pendingSnapshotResolver = null;
                        resolve(applied);
                    };

                    this.postToIframe(MSG.REQUEST_SNAPSHOT, {});
                });
            },

            async onMessage(event) {
                const iframe = this.$refs?.zoneIframe;
                const fromOurIframe =
                    iframe?.contentWindow && event.source === iframe.contentWindow;
                // На shared hosting origin иногда расходится с location — доверяем source iframe.
                if (!fromOurIframe && event.origin !== window.location.origin) {
                    return;
                }

                const data = event.data;
                if (!data || typeof data.type !== 'string') {
                    return;
                }

                if (data.type === MSG.READY) {
                    this.markIframeReady();
                    this.statusMessage =
                        'Редактор готов. После правок: «Сохранить» в редакторе, затем «Сохранить» внизу страницы.';
                    return;
                }

                if (data.type === MSG.CHANGE) {
                    this.iframeInitialized = true;
                    const applied = await this.applyPayloadToWire(data.payload ?? {});
                    if (!applied) {
                        return;
                    }
                    const count = Array.isArray(data.payload?.zones)
                        ? data.payload.zones.length
                        : 0;
                    this.statusMessage =
                        count === 0
                            ? 'Зоны очищены в форме. Нажмите «Сохранить» внизу страницы.'
                            : `Зон в форме: ${count}. Нажмите «Сохранить» внизу страницы.`;
                    return;
                }

                if (data.type === MSG.SNAPSHOT) {
                    this.iframeInitialized = true;
                    const applied = await this.applyPayloadToWire(data.payload ?? {});
                    if (this.pendingSnapshotResolver) {
                        this.pendingSnapshotResolver(applied);
                    }
                }
            },
        }));

        window.deliveryZoneSyncBeforeSave = async function deliveryZoneSyncBeforeSave(wire) {
            const bridge = window.__activeDeliveryZoneBridge;

            if (bridge) {
                const synced = await bridge.syncFromIframe();
                const zones = bridge.resolveZones();
                const hasZones = Array.isArray(zones) && zones.length > 0;

                if (!synced && !hasZones) {
                    bridge.statusMessage =
                        'Не удалось синхронизировать зоны с картой. Сохранение отменено.';
                    return;
                }

                const applied = await bridge.applyZonesToWire(zones);
                if (!applied) {
                    return;
                }
            }

            wire.save();
        };
    </script>
@endscript
