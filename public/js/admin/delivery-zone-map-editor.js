/**
 * Iframe-редактор зоны бесплатной доставки (Яндекс.Карты 2.1).
 */
(function () {
    const C = window.GangstersYandexCoords;
    const TOMSK = C?.TOMSK_CENTER ?? [56.49771, 84.97437];
    const DEFAULT_ZOOM = 12;

    const MSG = {
        READY: "delivery-zone:ready",
        INIT: "delivery-zone:init",
        CHANGE: "delivery-zone:change",
    };

    function parseGeometry(raw) {
        if (raw == null || raw === "") {
            return null;
        }
        if (typeof raw === "object") {
            if (raw.type === "Feature" && raw.geometry) {
                return raw.geometry;
            }
            return raw.type ? raw : null;
        }
        try {
            const parsed = JSON.parse(raw);
            if (parsed?.type === "Feature" && parsed.geometry) {
                return parsed.geometry;
            }
            return parsed?.type ? parsed : null;
        } catch {
            return null;
        }
    }

    function roundCoord(value) {
        return Math.round(Number(value) * 1e6) / 1e6;
    }

    function coordsAlmostEqual(a, b) {
        return (
            Math.abs(a[0] - b[0]) < 1e-6 && Math.abs(a[1] - b[1]) < 1e-6
        );
    }

    function normalizeRingForStorage(coords) {
        if (!coords || coords.length < 3) {
            return null;
        }

        let ring = coords.map(([lat, lon]) => [
            roundCoord(lon),
            roundCoord(lat),
        ]);

        const first = ring[0];
        let last = ring[ring.length - 1];

        if (!coordsAlmostEqual(first, last)) {
            ring.push([first[0], first[1]]);
        } else if (ring.length > 4 && coordsAlmostEqual(first, last)) {
            ring = ring.slice(0, -1);
            ring.push([first[0], first[1]]);
        }

        if (ring.length < 4) {
            return null;
        }

        return ring;
    }

    function ymapsCoordsToGeometry(coords) {
        const ring = normalizeRingForStorage(coords);
        if (!ring) {
            return null;
        }
        return { type: "Polygon", coordinates: [ring] };
    }

    function postToParent(type, payload) {
        if (window.parent === window) {
            return;
        }
        window.parent.postMessage({ type, payload }, window.location.origin);
    }

    const editor = {
        config: window.__DELIVERY_ZONE_EDITOR__ || {},
        map: null,
        polygon: null,
        hasPolygon: false,
        mapBusy: false,

        els: {
            status: document.getElementById("status"),
            btnDraw: document.getElementById("btn-draw"),
            btnEdit: document.getElementById("btn-edit"),
            btnClear: document.getElementById("btn-clear"),
            btnCenter: document.getElementById("btn-center"),
            btnApply: document.getElementById("btn-apply"),
        },

        setStatus(text) {
            if (this.els.status) {
                this.els.status.textContent = text || "";
            }
        },

        updateButtons() {
            const busy = this.mapBusy;
            this.els.btnEdit.disabled = busy || !this.hasPolygon;
            this.els.btnClear.disabled = busy || !this.hasPolygon;
            this.els.btnApply.disabled = busy || !this.hasPolygon;
        },

        notifyChange(geometry) {
            postToParent(MSG.CHANGE, {
                geometry,
                kitchenLatitude: this.config.kitchenLatitude ?? null,
                kitchenLongitude: this.config.kitchenLongitude ?? null,
            });
        },

        applyExternalConfig(cfg) {
            if (!cfg || typeof cfg !== "object") {
                return;
            }
            if (cfg.address) {
                this.config.address = cfg.address;
            }
            if (cfg.kitchenLatitude != null) {
                this.config.kitchenLatitude = cfg.kitchenLatitude;
            }
            if (cfg.kitchenLongitude != null) {
                this.config.kitchenLongitude = cfg.kitchenLongitude;
            }
            if (cfg.geometry !== undefined) {
                const g = parseGeometry(cfg.geometry);
                if (this.map) {
                    const ring = C.geometryToYmapsRing(g);
                    if (ring.length >= 3 && C.ringIsNearTomsk(ring)) {
                        this.attachPolygon(ring, false);
                        this.fitPolygonBounds();
                    } else {
                        this.showTomsk();
                        this.setStatus(
                            "Сохранённая зона вне Томска — нарисуйте контур заново.",
                        );
                    }
                } else {
                    this.config.geometry = g;
                }
            }
        },

        fitPolygonBounds() {
            if (!this.map || !this.polygon) {
                return;
            }
            const coords = this.polygon.geometry?.getCoordinates?.()?.[0] || [];
            if (!C.ringIsNearTomsk(coords)) {
                this.map.setCenter(TOMSK, DEFAULT_ZOOM);
                return;
            }
            const bounds = this.polygon.geometry?.getBounds?.();
            if (bounds) {
                this.map.setBounds(bounds, {
                    checkZoomRange: true,
                    zoomMargin: 48,
                });
            }
        },

        resolveInitialCenter(savedGeometry) {
            const ring = savedGeometry ? C.geometryToYmapsRing(savedGeometry) : [];
            if (ring.length > 0 && C.isNearTomskArea(ring[0])) {
                return ring[0];
            }

            const kitchen = C.pairToYmapsCenter(
                this.config.kitchenLatitude,
                this.config.kitchenLongitude,
            );
            if (kitchen && C.isNearTomskArea(kitchen)) {
                return kitchen;
            }

            return TOMSK;
        },

        showTomsk() {
            if (!this.map) {
                return;
            }
            this.map.setCenter(TOMSK, DEFAULT_ZOOM);
        },

        geocodeAddress() {
            const query = String(this.config.address || "").trim();
            if (!query || typeof ymaps === "undefined") {
                return Promise.resolve(null);
            }
            return ymaps
                .geocode(query, { results: 1 })
                .then((res) => {
                    const first = res.geoObjects.get(0);
                    if (!first) {
                        return null;
                    }
                    const coords = first.geometry.getCoordinates();
                    if (!C.isNearTomskArea(coords)) {
                        return null;
                    }
                    this.config.kitchenLatitude = coords[0];
                    this.config.kitchenLongitude = coords[1];
                    return coords;
                })
                .catch(() => null);
        },

        attachPolygon(coords, startEditing) {
            if (!this.map) {
                return;
            }
            if (this.polygon) {
                this.map.geoObjects.remove(this.polygon);
                this.polygon = null;
            }
            if (!coords || coords.length < 3) {
                this.hasPolygon = false;
                this.updateButtons();
                this.notifyChange(null);
                return;
            }

            this.polygon = new ymaps.Polygon(
                [coords],
                {},
                {
                    editorDrawingCursor: "crosshair",
                    fillColor: "#C6242466",
                    strokeColor: "#C62424",
                    strokeWidth: 3,
                },
            );

            this.polygon.events.add("geometrychange", () => {
                this.syncFromPolygon(false);
            });

            this.map.geoObjects.add(this.polygon);
            this.hasPolygon = true;
            this.syncFromPolygon(false);

            if (startEditing && this.polygon.editor) {
                this.polygon.editor.startEditing();
            }
            this.updateButtons();
        },

        syncFromPolygon(notify) {
            if (!this.polygon) {
                this.hasPolygon = false;
                this.updateButtons();
                if (notify) {
                    this.notifyChange(null);
                }
                return;
            }
            const coords = this.polygon.geometry.getCoordinates()[0] || [];
            const geometry = ymapsCoordsToGeometry(coords);
            this.hasPolygon = Boolean(geometry);
            this.updateButtons();
            if (notify) {
                this.notifyChange(geometry);
            }
        },

        startDrawing() {
            if (!this.map || this.mapBusy) {
                return;
            }
            if (this.polygon) {
                this.map.geoObjects.remove(this.polygon);
            }
            this.polygon = new ymaps.Polygon(
                [],
                {},
                {
                    editorDrawingCursor: "crosshair",
                    fillColor: "#C6242466",
                    strokeColor: "#C62424",
                    strokeWidth: 3,
                },
            );
            this.polygon.events.add("geometrychange", () => {
                this.syncFromPolygon(false);
            });
            this.map.geoObjects.add(this.polygon);
            this.polygon.editor.startDrawing();
            this.hasPolygon = true;
            this.updateButtons();
            this.setStatus("Кликайте по карте. Двойной клик — завершить контур.");
        },

        startEditing() {
            this.polygon?.editor?.startEditing();
            this.setStatus("Редактирование вершин.");
        },

        clearZone() {
            if (this.polygon && this.map) {
                this.map.geoObjects.remove(this.polygon);
            }
            this.polygon = null;
            this.hasPolygon = false;
            this.updateButtons();
            this.notifyChange(null);
            this.map?.setCenter(TOMSK, DEFAULT_ZOOM);
            this.setStatus("Зона очищена. Карта — Томск.");
        },

        async centerOnAddress() {
            if (!this.map || this.mapBusy) {
                return;
            }
            this.mapBusy = true;
            this.updateButtons();
            try {
                const coords = await this.geocodeAddress();
                if (!coords) {
                    this.map.setCenter(TOMSK, DEFAULT_ZOOM);
                    this.setStatus("Адрес не найден. Показан Томск — заполните адрес в «Компании».");
                    return;
                }
                this.map.setCenter(coords, 14);
                this.setStatus("Карта центрирована по адресу кухни.");
            } finally {
                this.mapBusy = false;
                this.updateButtons();
            }
        },

        applyToParent() {
            if (!this.polygon) {
                this.setStatus("Сначала нарисуйте зону на карте.");
                return;
            }
            const coords = this.polygon.geometry.getCoordinates()[0] || [];
            const geometry = ymapsCoordsToGeometry(coords);
            if (!geometry) {
                this.setStatus(
                    "Контур слишком мал или невалиден. Нужно минимум 3 вершины.",
                );
                return;
            }
            this.hasPolygon = true;
            this.updateButtons();
            this.notifyChange(geometry);
            this.setStatus("Зона отправлена в форму. Не забудьте сохранить запись.");
        },

        async bootstrap() {
            this.mapBusy = true;
            this.updateButtons();
            try {
                const saved = parseGeometry(this.config.geometry);

                this.map = new ymaps.Map("map", {
                    center: TOMSK,
                    zoom: DEFAULT_ZOOM,
                    controls: ["zoomControl", "typeSelector"],
                });
                this.map.container.fitToViewport();

                if (saved) {
                    const ring = C.geometryToYmapsRing(saved);
                    if (ring.length >= 3 && C.ringIsNearTomsk(ring)) {
                        this.attachPolygon(ring, false);
                        this.fitPolygonBounds();
                        this.setStatus(
                            "Зона загружена. «Применить» — записать в форму.",
                        );
                    } else {
                        this.showTomsk();
                        this.setStatus(
                            saved
                                ? "Зона вне Томска или битая — нарисуйте заново."
                                : "Старт: Томск. Нарисуйте зону бесплатной доставки.",
                        );
                    }
                } else {
                    const geocoded = await this.geocodeAddress();
                    if (geocoded) {
                        this.map.setCenter(geocoded, 14);
                        this.setStatus(
                            "Старт по адресу кухни. Нарисуйте зону бесплатной доставки.",
                        );
                    } else {
                        this.showTomsk();
                        this.setStatus(
                            "Старт: Томск. Нарисуйте зону бесплатной доставки.",
                        );
                    }
                }
            } catch (error) {
                console.error(error);
                this.setStatus("Ошибка инициализации карты.");
            } finally {
                this.mapBusy = false;
                this.updateButtons();
                postToParent(MSG.READY, {});
            }
        },

        bindUi() {
            this.els.btnDraw?.addEventListener("click", () => this.startDrawing());
            this.els.btnEdit?.addEventListener("click", () => this.startEditing());
            this.els.btnClear?.addEventListener("click", () => this.clearZone());
            this.els.btnCenter?.addEventListener("click", () => this.centerOnAddress());
            this.els.btnApply?.addEventListener("click", () => this.applyToParent());
        },

        listenParent() {
            window.addEventListener("message", (event) => {
                if (event.origin !== window.location.origin) {
                    return;
                }
                const data = event.data;
                if (!data || typeof data.type !== "string") {
                    return;
                }
                if (data.type === MSG.INIT) {
                    this.applyExternalConfig(data.payload);
                }
            });
        },

        init() {
            this.bindUi();
            this.listenParent();
            if (typeof ymaps === "undefined" || !C) {
                this.setStatus("Не загружен API карт или модуль координат.");
                return;
            }
            ymaps.ready(() => this.bootstrap());
        },
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => editor.init());
    } else {
        editor.init();
    }
})();
