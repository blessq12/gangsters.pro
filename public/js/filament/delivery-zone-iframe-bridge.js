/**
 * Мост postMessage между Filament-формой и iframe редактора зон.
 */
(function () {
    const MSG = {
        READY: "delivery-zone:ready",
        INIT: "delivery-zone:init",
        CHANGE: "delivery-zone:change",
        REQUEST_SNAPSHOT: "delivery-zone:request-snapshot",
        SNAPSHOT: "delivery-zone:snapshot",
    };

    function cloneForPostMessage(value) {
        if (value === undefined || value === null) {
            return null;
        }

        if (
            typeof value === "string" ||
            typeof value === "number" ||
            typeof value === "boolean"
        ) {
            return value;
        }

        try {
            return JSON.parse(JSON.stringify(value));
        } catch {
            return null;
        }
    }

    function cloneMessagePayload(payload) {
        const cloned = cloneForPostMessage(payload);

        return cloned && typeof cloned === "object" ? cloned : {};
    }

    function registerDeliveryZoneBridge() {
        Alpine.data("deliveryZoneBridge", (config = {}) => ({
            state: null,
            initialPayload: config.initialPayload ?? null,
            zonesStatePath: config.zonesStatePath ?? "data.delivery_zones",
            kitchenAddressPath:
                config.kitchenAddressPath ?? "data.kitchen_address",
            kitchenLatPath:
                config.kitchenLatPath ?? "data.kitchen_latitude",
            kitchenLngPath:
                config.kitchenLngPath ?? "data.kitchen_longitude",
            iframeReady: false,
            iframeInitialized: false,
            statusMessage: "",
            pendingSnapshotResolver: null,

            init() {
                window.__activeDeliveryZoneBridge = this;

                if (this.$wire && this.zonesStatePath) {
                    this.state = this.$wire.$entangle(this.zonesStatePath).live;
                } else if (Array.isArray(this.initialPayload?.zones)) {
                    this.state = this.initialPayload.zones;
                }

                this.$nextTick(() => {
                    this.observeIframeVisibility();
                    this.pushInitToIframe();
                });
            },

            observeIframeVisibility() {
                const iframe = this.$refs?.zoneIframe;
                if (!iframe || !("IntersectionObserver" in window)) {
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    // Только первый показ — повторный INIT затирает правки в iframe.
                    if (
                        !this.iframeInitialized
                        && entries.some((entry) => entry.isIntersecting)
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

                if (!path.startsWith("data.")) {
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

            markIframeReady() {
                this.iframeReady = true;
                if (!this.iframeInitialized) {
                    this.pushInitToIframe();
                    // Сразу помечаем: повторный INIT затрёт правки в iframe.
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

            applyPayloadToWire(payload) {
                const zones = Array.isArray(payload?.zones)
                    ? payload.zones
                    : [];

                const applied = this.applyZonesToWire(zones);
                if (!applied) {
                    return false;
                }

                // Координаты кухни редактор зон больше не меняет — только читает.
                return true;
            },

            applyZonesToWire(zones) {
                if (!this.$wire?.set) {
                    this.statusMessage =
                        "Нет связи с формой. Обновите страницу.";
                    return false;
                }

                const plainZones = cloneForPostMessage(zones) || [];
                this.state = plainZones;
                this.$wire.set(this.zonesStatePath, plainZones);
                return true;
            },

            syncFromIframe(timeoutMs = 2500) {
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

            onMessage(event) {
                if (event.origin !== window.location.origin) {
                    return;
                }

                const data = event.data;
                if (!data || typeof data.type !== "string") {
                    return;
                }

                if (data.type === MSG.READY) {
                    this.markIframeReady();
                    this.statusMessage =
                        "Редактор готов: список зон слева, карта Томска справа. После правок нажмите «Сохранить» в редакторе, затем «Сохранить» внизу страницы.";
                    return;
                }

                if (data.type === MSG.CHANGE) {
                    this.iframeInitialized = true;
                    const applied = this.applyPayloadToWire(
                        data.payload ?? {},
                    );

                    if (!applied) {
                        return;
                    }

                    const count = Array.isArray(data.payload?.zones)
                        ? data.payload.zones.length
                        : 0;
                    this.statusMessage =
                        count === 0
                            ? "Зоны очищены."
                            : `Зон: ${count}. Нажмите «Сохранить» внизу страницы.`;
                    return;
                }

                if (data.type === MSG.SNAPSHOT) {
                    this.iframeInitialized = true;
                    const applied = this.applyPayloadToWire(
                        data.payload ?? {},
                    );

                    if (this.pendingSnapshotResolver) {
                        this.pendingSnapshotResolver(applied);
                    }
                }
            },
        }));
    }

    window.deliveryZoneSyncBeforeSave = async function deliveryZoneSyncBeforeSave(
        wire,
    ) {
        const bridge = window.__activeDeliveryZoneBridge;

        if (bridge) {
            const synced = await bridge.syncFromIframe();
            const zones = bridge.resolveZones();
            const hasZones = Array.isArray(zones) && zones.length > 0;

            // Sync упал и в форме пусто — не пишем [] поверх данных (типичный сбой origin/iframe).
            if (!synced && !hasZones) {
                bridge.statusMessage =
                    "Не удалось синхронизировать зоны с картой. Сохранение отменено. Проверь, что редактор загрузился на том же домене, что и админка.";
                return;
            }
        }

        wire.save();
    };

    if (window.Alpine) {
        registerDeliveryZoneBridge();
    } else {
        document.addEventListener("alpine:init", registerDeliveryZoneBridge);
    }

    window.addEventListener("message", (event) => {
        if (event.origin !== window.location.origin) {
            return;
        }

        const bridge = window.__activeDeliveryZoneBridge;
        if (bridge) {
            bridge.onMessage(event);
        }
    });
})();
