import { nextTick, ref } from "vue";
import {
    TOMSK_CENTER,
    loadYandexMapsApi,
    readYandexMapsApiKey,
    resolveMapCenter,
} from "../maps/yandexMapsApi.js";

/**
 * Жизненный цикл ymaps.Map: инстанс только в замыкании (не ref/reactive).
 *
 * @param {{
 *   controls?: string[],
 *   zoom?: number,
 *   missingKeyMessage?: string,
 * }} [options]
 */
export function useYandexMap(options = {}) {
    const controls = Array.isArray(options.controls) ? options.controls : ["zoomControl"];
    const defaultZoom = Number.isFinite(Number(options.zoom)) ? Number(options.zoom) : 12;
    const missingKeyMessage =
        options.missingKeyMessage || "Нет VITE_YANDEX_MAPS_API_KEY — карта недоступна";

    const mapHost = ref(/** @type {HTMLElement | null} */ (null));
    const status = ref(/** @type {"idle"|"loading"|"ready"|"error"} */ ("idle"));
    const statusText = ref("");

    /** @type {any} */
    let map = null;
    let destroyed = false;

    const apiKey = readYandexMapsApiKey();

    function isDestroyed() {
        return destroyed;
    }

    function getMap() {
        return map;
    }

    function resetDestroyed() {
        destroyed = false;
    }

    function markDestroyed() {
        destroyed = true;
    }

    /**
     * @param {HTMLElement} element
     * @param {number} [attempts]
     * @returns {Promise<void>}
     */
    function waitForHostSize(element, attempts = 30) {
        return new Promise((resolve, reject) => {
            const tick = (left) => {
                if (destroyed) {
                    reject(new Error("Компонент размонтирован"));
                    return;
                }
                const width = element.offsetWidth;
                const height = element.offsetHeight;
                if (width > 0 && height > 0) {
                    resolve();
                    return;
                }
                if (left <= 0) {
                    reject(new Error("Контейнер карты без размеров"));
                    return;
                }
                requestAnimationFrame(() => tick(left - 1));
            };
            tick(attempts);
        });
    }

    /**
     * @param {{
     *   center?: [number, number]|null,
     *   city?: string|null,
     *   latitude?: number|null,
     *   longitude?: number|null,
     *   zoom?: number,
     *   waitForSize?: boolean,
     * }} [mountOptions]
     * @returns {Promise<any|null>}
     */
    async function mountMap(mountOptions = {}) {
        if (!mapHost.value) {
            status.value = "error";
            statusText.value = "Нет контейнера карты";
            return null;
        }
        if (!apiKey) {
            status.value = "error";
            statusText.value = missingKeyMessage;
            return null;
        }

        status.value = "loading";
        statusText.value = "Загрузка карты…";

        try {
            await loadYandexMapsApi(apiKey);
            if (destroyed) {
                return null;
            }
            await nextTick();
            const host = mapHost.value;
            if (destroyed || !host) {
                return null;
            }

            if (mountOptions.waitForSize !== false) {
                await waitForHostSize(host);
                if (destroyed) {
                    return null;
                }
            }

            let center = mountOptions.center ?? null;
            if (!Array.isArray(center) || center.length < 2) {
                center = await resolveMapCenter({
                    latitude: mountOptions.latitude ?? null,
                    longitude: mountOptions.longitude ?? null,
                    city: mountOptions.city ?? "",
                });
            }
            if (destroyed || !mapHost.value) {
                return null;
            }

            const zoom = Number.isFinite(Number(mountOptions.zoom))
                ? Number(mountOptions.zoom)
                : defaultZoom;

            map = new window.ymaps.Map(
                mapHost.value,
                {
                    center: center || TOMSK_CENTER,
                    zoom,
                    controls,
                },
                {
                    suppressMapOpenBlock: true,
                },
            );
            map.container.fitToViewport();
            status.value = "ready";
            statusText.value = "";
            return map;
        } catch (err) {
            destroyMap();
            if (!destroyed) {
                status.value = "error";
                statusText.value = err instanceof Error ? err.message : "Ошибка карты";
            }
            return null;
        }
    }

    function destroyMap() {
        try {
            if (map && typeof map.destroy === "function") {
                map.destroy();
            }
        } catch {
            /* ymaps / unmount */
        }
        map = null;
    }

    function fitToViewport() {
        if (destroyed || !map) {
            return;
        }
        try {
            map.container.fitToViewport();
        } catch {
            /* noop */
        }
    }

    return {
        mapHost,
        status,
        statusText,
        apiKey,
        mountMap,
        destroyMap,
        getMap,
        isDestroyed,
        resetDestroyed,
        markDestroyed,
        waitForHostSize,
        fitToViewport,
    };
}
