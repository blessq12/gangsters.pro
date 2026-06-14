import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import {
    buildYandexMapKitchenPointWidgetUrl,
    kitchenAddressLabelOrFallback,
} from "../../utils/system/companyDeliveryFacts";
import { mountCompanyKitchenPlacemarkReadonlyMap } from "../../utils/maps/companyKitchenMap";
import { readYandexMapsApiKeyFromSite } from "../../utils/maps/mountYandexDeliveryZoneReadonlyMap";
import { resolveDeliveryDockMapMode } from "../../utils/maps/resolveDeliveryDockMapMode";

/**
 * Readonly-карта адреса кухни (Яндекс SDK / виджет / fallback).
 *
 * @param {{
 *   facts: import('vue').ComputedRef<object|null|undefined>,
 *   deliveryLoading: import('vue').ComputedRef<boolean>,
 * }} options
 */
export function useKitchenAddressReadonlyMap({ facts, deliveryLoading }) {
    const mapUrl = computed(() => buildYandexMapKitchenPointWidgetUrl(facts.value));

    const addressLabel = computed(() =>
        kitchenAddressLabelOrFallback(facts.value),
    );

    const hasKitchenPoint = computed(() => Boolean(addressLabel.value));

    const hasMapsApiKey = computed(() => Boolean(readYandexMapsApiKeyFromSite()));

    const mapDisplayMode = computed(() =>
        resolveDeliveryDockMapMode({
            hasApiKey: hasMapsApiKey.value,
            hasAddress: hasKitchenPoint.value,
            isLoading: deliveryLoading.value && !facts.value,
        }),
    );

    const mapContainerRef = ref(null);
    /** @type {import('vue').Ref<{ destroy: () => void, refit: () => void, placemarkShown?: boolean }|null>} */
    const mapController = ref(null);
    const mapMountFailed = ref(false);
    const placemarkShown = ref(false);

    /** @type {import('vue').Ref<ResizeObserver|null>} */
    const mapResizeObserver = ref(null);

    const showPlacemarkHint = computed(
        () =>
            mapDisplayMode.value === "zone-sdk" &&
            !placemarkShown.value &&
            !mapMountFailed.value &&
            mapController.value != null,
    );

    function disconnectResizeObserver() {
        mapResizeObserver.value?.disconnect();
        mapResizeObserver.value = null;
    }

    function connectResizeObserver() {
        disconnectResizeObserver();
        if (!mapContainerRef.value || !mapController.value) {
            return;
        }
        mapResizeObserver.value = new ResizeObserver(() => {
            mapController.value?.refit?.();
        });
        mapResizeObserver.value.observe(mapContainerRef.value);
    }

    function destroyMap() {
        disconnectResizeObserver();
        mapController.value?.destroy();
        mapController.value = null;
        placemarkShown.value = false;
    }

    async function syncMap() {
        destroyMap();
        mapMountFailed.value = false;

        if (mapDisplayMode.value !== "zone-sdk" || !mapContainerRef.value) {
            return;
        }

        try {
            const controller = await mountCompanyKitchenPlacemarkReadonlyMap(
                mapContainerRef.value,
                facts.value,
            );
            if (!controller) {
                mapMountFailed.value = true;
                return;
            }
            mapController.value = controller;
            placemarkShown.value = controller.placemarkShown === true;
            connectResizeObserver();
            controller.refit?.();
        } catch (error) {
            console.error("Kitchen address map:", error);
            mapMountFailed.value = true;
        }
    }

    watch([mapDisplayMode, facts], async () => {
        await nextTick();
        if (mapDisplayMode.value === "zone-sdk") {
            void syncMap();
        } else {
            destroyMap();
            mapMountFailed.value = false;
        }
    });

    onMounted(() => {
        void nextTick().then(() => syncMap());
    });

    onUnmounted(() => {
        destroyMap();
        mapMountFailed.value = false;
    });

    return {
        mapUrl,
        addressLabel,
        mapDisplayMode,
        mapContainerRef,
        mapMountFailed,
        showPlacemarkHint,
    };
}
