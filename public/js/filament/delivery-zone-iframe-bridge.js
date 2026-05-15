/**
 * Мост postMessage между Filament-формой и iframe редактора зоны.
 */
(function () {
    const MSG = {
        READY: "delivery-zone:ready",
        INIT: "delivery-zone:init",
        CHANGE: "delivery-zone:change",
    };

    document.addEventListener("alpine:init", () => {
        Alpine.data("deliveryZoneBridge", () => ({
            state: null,
            geometryStatePath: "data.delivery_zone_geojson",
            kitchenAddress: "",
            kitchenLatPath: "data.kitchen_latitude",
            kitchenLngPath: "data.kitchen_longitude",
            iframeReady: false,
            statusMessage: "",

            init() {
                if (this.$wire && this.geometryStatePath) {
                    this.state = this.$wire.$entangle(this.geometryStatePath);
                }
                window.addEventListener("message", (event) => this.onMessage(event));
            },

            buildInitPayload() {
                const data = this.$wire?.data ?? {};

                return {
                    geometry: this.state ?? null,
                    address: this.kitchenAddress,
                    kitchenLatitude: data.kitchen_latitude ?? null,
                    kitchenLongitude: data.kitchen_longitude ?? null,
                };
            },

            postToIframe(type, payload) {
                const iframe = this.$refs?.zoneIframe;
                if (!iframe?.contentWindow) {
                    return;
                }
                iframe.contentWindow.postMessage(
                    { type, payload },
                    window.location.origin,
                );
            },

            onIframeLoad() {
                if (this.iframeReady) {
                    this.postToIframe(MSG.INIT, this.buildInitPayload());
                }
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

                this.state = geometry;
                this.$wire.set(this.geometryStatePath, geometry);
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

            onMessage(event) {
                if (event.origin !== window.location.origin) {
                    return;
                }
                const data = event.data;
                if (!data || typeof data.type !== "string") {
                    return;
                }

                if (data.type === MSG.READY) {
                    this.iframeReady = true;
                    this.postToIframe(MSG.INIT, this.buildInitPayload());
                    this.statusMessage =
                        "Редактор готов. «Применить» в iframe, затем «Сохранить» в форме.";
                    return;
                }

                if (data.type === MSG.CHANGE) {
                    const geometry = data.payload?.geometry ?? null;

                    if (geometry == null) {
                        this.applyGeometryToWire(null);
                        this.applyKitchenCoords(
                            data.payload?.kitchenLatitude ?? null,
                            data.payload?.kitchenLongitude ?? null,
                        );
                        this.statusMessage = "Зона очищена.";
                        return;
                    }

                    const applied = this.applyGeometryToWire(geometry);
                    if (!applied) {
                        return;
                    }

                    this.applyKitchenCoords(
                        data.payload?.kitchenLatitude ?? null,
                        data.payload?.kitchenLongitude ?? null,
                    );
                    this.statusMessage =
                        "Зона обновлена. Нажмите «Сохранить» внизу страницы.";
                }
            },
        }));
    });
})();
