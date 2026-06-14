<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useDeliveryReadModel } from "../../../../features/delivery/useDeliveryReadModel";
import {
    buildDefinedDeliveryStats,
    buildYandexMapWidgetSearchUrl,
} from "../../../../utils/system/companyDeliveryFacts";
import {
    hasDeliveryZoneGeometry,
    mountCompanyDeliveryZoneReadonlyMap,
} from "../../../../utils/maps/companyDeliveryZoneMap";
import { readYandexMapsApiKeyFromSite } from "../../../../utils/maps/mountYandexDeliveryZoneReadonlyMap";
import { resolveDeliveryDockMapMode } from "../../../../utils/maps/resolveDeliveryDockMapMode";

const DOCK_PANEL_ANIM_MS = 300;

const panels = useAppDesign().components.dockPanels;
const d = panels.delivery;

const { facts: factsRef, loading: deliveryLoading } = useDeliveryReadModel({
    autoload: true,
});

const facts = computed(() => factsRef.value);

const mapUrl = computed(() => buildYandexMapWidgetSearchUrl(facts.value));

const hasZoneGeometry = computed(() => hasDeliveryZoneGeometry(facts.value));

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
/** @type {import('vue').Ref<{ destroy: () => void, refit: () => void }|null>} */
const zoneMapController = ref(null);
const zoneMapMountFailed = ref(false);
const zonePolygonShown = ref(false);

/** @type {import('vue').Ref<ResizeObserver|null>} */
const mapResizeObserver = ref(null);
/** @type {ReturnType<typeof setTimeout>|null} */
let mountDelayTimer = null;

const isLoadingDelivery = computed(
    () => deliveryLoading.value && !facts.value,
);

const showZonePolygonHint = computed(
    () =>
        mapDisplayMode.value === "zone-sdk" &&
        hasZoneGeometry.value &&
        !zonePolygonShown.value &&
        !zoneMapMountFailed.value &&
        zoneMapController.value != null,
);

const dockStats = computed(() => {
    if (isLoadingDelivery.value) {
        return [];
    }
    return buildDefinedDeliveryStats(facts.value);
});

const showStatsRow = computed(
    () =>
        dockStats.value.length > 0 ||
        isLoadingDelivery.value ||
        showZonePolygonHint.value,
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
        mountDelayTimer = setTimeout(() => {
            mountDelayTimer = null;
            void nextTick().then(() => syncZoneMap());
        }, DOCK_PANEL_ANIM_MS);
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
</script>

<template>
    <DockPanelLayout
        title="Оплата и доставка"
        :body-class="d.mapBodyOverride"
    >
        <div :class="d.mapStage">
            <div
                v-if="mapDisplayMode === 'zone-sdk'"
                :class="d.mapLayer"
            >
                <div
                    v-if="zoneMapMountFailed"
                    :class="d.mapFallback"
                >
                    <p :class="d.mapFallbackProse">
                        Не удалось загрузить карту. Обновите страницу или проверьте
                        <code class="text-xs">YANDEX_MAPS_API_KEY</code>.
                    </p>
                </div>
                <div
                    v-else
                    ref="mapContainerRef"
                    :class="d.mapIframe"
                    role="img"
                    aria-label="Карта зоны доставки"
                />
            </div>
            <div
                v-else-if="mapDisplayMode === 'widget'"
                :class="d.mapLayer"
            >
                <iframe
                    :src="mapUrl"
                    title="Карта — адрес кухни"
                    :class="d.mapIframe"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                />
            </div>
            <div
                v-else-if="mapDisplayMode === 'loading'"
                :class="d.mapLoadingBox"
            >
                Загрузка…
            </div>
            <div
                v-else
                :class="d.mapFallback"
            >
                <p :class="d.mapFallbackProse">
                    Карта недоступна без адреса кухни в настройках.
                </p>
            </div>

            <div
                v-if="showStatsRow"
                :class="d.overlayTop"
            >
                <div
                    v-if="showZonePolygonHint"
                    :class="[d.island, 'basis-full']"
                >
                    <p :class="d.statLabel">
                        Зона доставки
                    </p>
                    <p :class="[d.statValue, 'text-sm font-normal']">
                        Зона на карте не отображается — проверьте полигон в админке.
                    </p>
                </div>
                <div
                    v-if="isLoadingDelivery"
                    :class="[d.island, d.statSkeletonCard]"
                >
                    <p :class="d.statLabel">
                        Данные
                    </p>
                    <p :class="d.statValueSkeleton">
                        …
                    </p>
                </div>
                <template v-else>
                    <div
                        v-for="stat in dockStats"
                        :key="stat.label"
                        :class="[d.island, d.statCard]"
                    >
                        <p :class="d.statLabel">
                            {{ stat.label }}
                        </p>
                        <p :class="[d.statValue, 'wrap-break-word']">
                            {{ stat.value }}
                        </p>
                    </div>
                </template>
            </div>
        </div>
    </DockPanelLayout>
</template>

<style scoped>
.wrap-break-word {
    overflow-wrap: anywhere;
    word-break: break-word;
}
</style>
