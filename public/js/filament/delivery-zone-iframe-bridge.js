/**
 * Мост postMessage между Filament-формой и iframe редактора зоны.
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
        if (value === undefined) {
            return null;
        }

        if (value === null) {
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
            geometryStatePath:
                config.geometryStatePath ?? "data.delivery_zone_geojson",
            kitchenAddress: "",
            kitchenAddressPath:
                config.kitchenAddressPath ?? "data.kitchen_address",
            kitchenLatPath:
                config.kitchenLatPath ?? "data.kitchen_latitude",
            kitchenLngPath:
                config.kitchenLngPath ?? "data.kitchen_longitude",
            iframeReady: false,
            iframeDomReady: false,
            statusMessage: "",
            pendingSnapshotResolver: null,
            readyHeartbeatTimer: null,

            init() {
                window.__activeDeliveryZoneBridge = this;

                if (this.$wire) {
                    if (this.geometryStatePath) {
                        this.state = this.$wire.$entangle(
                            this.geometryStatePath,
                        ).live;
                    }

                    this.kitchenAddress = this.$wire.$entangle(
                        this.kitchenAddressPath,
                    ).live;
                } else if (this.initialPayload?.geometry) {
                    this.state = this.initialPayload.geometry;
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
                    if (entries.some((entry) => entry.isIntersecting)) {
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

            resolveGeometry() {
                if (
                    this.initialPayload?.geometry &&
                    typeof this.initialPayload.geometry === "object" &&
                    this.initialPayload.geometry.type
                ) {
                    return cloneForPostMessage(this.initialPayload.geometry);
                }

                const fromWire = this.readWireValue(this.geometryStatePath);
                if (
                    fromWire &&
                    typeof fromWire === "object" &&
                    fromWire.type
                ) {
                    return cloneForPostMessage(fromWire);
                }

                const fromEntangle = this.state;
                if (
                    fromEntangle &&
                    typeof fromEntangle === "object" &&
                    fromEntangle.type
                ) {
                    return cloneForPostMessage(fromEntangle);
                }

                return null;
            },

            buildInitPayload() {
                const data = this.$wire?.data ?? {};

                return {
                    geometry: this.resolveGeometry(),
                    address: String(
                        this.kitchenAddress ??
                            data.kitchen_address ??
                            this.initialPayload?.address ??
                            "",
                    ),
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
                this.pushInitToIframe();
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
                this.iframeDomReady = true;
                this.pushInitToIframe();
            },

            applyPayloadToWire(payload) {
                const geometry = payload?.geometry ?? null;

                if (geometry == null) {
                    this.applyGeometryToWire(null);
                } else {
                    const applied = this.applyGeometryToWire(geometry);
                    if (!applied) {
                        return false;
                    }
                }

                this.applyKitchenCoords(
                    payload?.kitchenLatitude ?? null,
                    payload?.kitchenLongitude ?? null,
                );

                return true;
            },

            applyGeometryToWire(geometry) {
                if (!this.$wire?.set) {
                    this.statusMessage =
                        "Нет связи с формой. Обновите страницу.";
                    return false;
                }

                if (geometry == null || geometry === "") {
                    this.state = null;
                    this.$wire.set(this.geometryStatePath, null);
                    return true;
                }

                const plainGeometry = cloneForPostMessage(geometry);
                this.state = plainGeometry;
                this.$wire.set(this.geometryStatePath, plainGeometry);
                return true;
            },

            applyKitchenCoords(lat, lng) {
                if (!this.$wire?.set) {
                    return;
                }

                if (lat != null) {
                    this.$wire.set(this.kitchenLatPath, lat);
                }

                if (lng != null) {
                    this.$wire.set(this.kitchenLngPath, lng);
                }
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
                        "Редактор готов. Нарисуйте зону и нажмите «Сохранить» — геометрия подтянется автоматически.";
                    return;
                }

                if (data.type === MSG.CHANGE) {
                    const applied = this.applyPayloadToWire(
                        data.payload ?? {},
                    );

                    if (!applied) {
                        return;
                    }

                    this.statusMessage =
                        data.payload?.geometry == null
                            ? "Зона очищена."
                            : "Зона обновлена. Нажмите «Сохранить» внизу страницы.";
                    return;
                }

                if (data.type === MSG.SNAPSHOT) {
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
            await bridge.syncFromIframe();
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
