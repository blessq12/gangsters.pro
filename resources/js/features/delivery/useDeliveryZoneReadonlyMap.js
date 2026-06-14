import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { buildYandexMapWidgetSearchUrl } from "../../utils/system/companyDeliveryFacts";
import {
    hasDeliveryZoneGeometry,
    mountCompanyDeliveryZoneReadonlyMap,
} from "../../utils/maps/companyDeliveryZoneMap";
import { readYandexMapsApiKeyFromSite } from "../../utils/maps/mountYandexDeliveryZoneReadonlyMap";
import { resolveDeliveryDockMapMode } from "../../utils/maps/resolveDeliveryDockMapMode";

/**
 * Readonly-карта зоны доставки (Яндекс SDK / виджет / fallback).
 *
 * @param {{
 *   facts: import('vue').ComputedRef<object|null|undefined>,
 *   deliveryLoading: import('vue').ComputedRef<boolean>,
 *   mountDelayMs?: number,
 * }} options
 */
export function useDeliveryZoneReadonlyMap({
    facts,
    deliveryLoading,
    mountDelayMs = 0,
}) {
    const mapUrl = computed(() => buildYandexMapWidgetSearchUrl(facts.value));

    const hasZoneGeometry = computed(() =>
        hasDeliveryZoneGeometry(facts.value),
    );

    const hasMapsApiKey = computed(() => Boolean(readYandexMapsApiKeyFromSite()));

    const hasKitchenAddress = computed(() => Boolean(mapUrl.value));

    const mapDisplayMode = computed(() =>
        resolveDeliveryDockMapMode({
            hasApiKey: hasMapsApiKey.value,
            hasAddress: hasKitchenAddress.value,
            isLoading: deliveryLoading.value && !facts.value,
        }),
    );

    const mapContainerRef = ref(null);
    /** @type {import('vue').Ref<{ destroy: () => void, refit: () => void, polygonShown?: boolean }|null>} */
    const zoneMapController = ref(null);
    const zoneMapMountFailed = ref(false);
    const zonePolygonShown = ref(false);

    /** @type {import('vue').Ref<ResizeObserver|null>} */
    const mapResizeObserver = ref(null);
    /** @type {ReturnType<typeof setTimeout>|null} */
    let mountDelayTimer = null;

    const showZonePolygonHint = computed(
        () =>
            mapDisplayMode.value === "zone-sdk" &&
            hasZoneGeometry.value &&
            !zonePolygonShown.value &&
            !zoneMapMountFailed.value &&
            zoneMapController.value != null,
    );

    function disconnectResizeObserver() {
        mapResizeObserver.value?.disconnect();
        mapResizeObserver.value = null;
    }

    function clearMountDelayTimer() {
        if (mountDelayTimer != null) {
            clearTimeout(mountDelayTimer);
            mountDelayTimer = null;
        }
    }

    function connectResizeObserver() {
        disconnectResizeObserver();
        if (!mapContainerRef.value || !zoneMapController.value) {
            return;
        }
        mapResizeObserver.value = new ResizeObserver(() => {
            zoneMapController.value?.refit?.();
        });
        mapResizeObserver.value.observe(mapContainerRef.value);
    }

    function destroyZoneMap() {
        disconnectResizeObserver();
        zoneMapController.value?.destroy();
        zoneMapController.value = null;
        zonePolygonShown.value = false;
    }

    async function syncZoneMap() {
        destroyZoneMap();
        zoneMapMountFailed.value = false;

        if (mapDisplayMode.value !== "zone-sdk" || !mapContainerRef.value) {
            return;
        }

        try {
            const controller = await mountCompanyDeliveryZoneReadonlyMap(
                mapContainerRef.value,
                facts.value,
            );
            if (!controller) {
                zoneMapMountFailed.value = true;
                return;
            }
            zoneMapController.value = controller;
            zonePolygonShown.value = controller.polygonShown === true;
            connectResizeObserver();
            controller.refit?.();
        } catch (error) {
            console.error("Delivery zone map:", error);
            zoneMapMountFailed.value = true;
        }
    }

    function scheduleSyncZoneMap() {
        clearMountDelayTimer();
        requestAnimationFrame(() => {
            if (mountDelayMs <= 0) {
                void nextTick().then(() => syncZoneMap());
                return;
            }
            mountDelayTimer = setTimeout(() => {
                mountDelayTimer = null;
                void nextTick().then(() => syncZoneMap());
            }, mountDelayMs);
        });
    }

    watch([mapDisplayMode, facts], async () => {
        await nextTick();
        if (mapDisplayMode.value === "zone-sdk") {
            scheduleSyncZoneMap();
        } else {
            clearMountDelayTimer();
            destroyZoneMap();
            zoneMapMountFailed.value = false;
        }
    });

    onMounted(() => {
        scheduleSyncZoneMap();
    });

    onUnmounted(() => {
        clearMountDelayTimer();
        destroyZoneMap();
        zoneMapMountFailed.value = false;
    });

    return {
        mapUrl,
        mapDisplayMode,
        mapContainerRef,
        zoneMapMountFailed,
        showZonePolygonHint,
    };
}
