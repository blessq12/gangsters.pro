<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useDeliveryReadModel } from "../../features/delivery/useDeliveryReadModel";
import { useDeliveryZoneReadonlyMap } from "../../features/delivery/useDeliveryZoneReadonlyMap";

const dm = useAppDesign().components.pages.delivery;

const { facts: factsRef, loading: deliveryLoading } = useDeliveryReadModel({
    autoload: true,
});

const facts = computed(() => factsRef.value);

const {
    mapUrl,
    mapDisplayMode,
    mapContainerRef,
    zoneMapMountFailed,
    showZonePolygonHint,
} = useDeliveryZoneReadonlyMap({
    facts,
    deliveryLoading,
});
</script>

<template>
    <SecondaryContentBlock
        title="Зона доставки"
        subtitle="НА КАРТЕ"
    >
        <div :class="dm.zoneMapStage">
            <div
                v-if="mapDisplayMode === 'zone-sdk'"
                :class="dm.zoneMapLayer"
            >
                <div
                    v-if="zoneMapMountFailed"
                    :class="dm.zoneMapFallback"
                >
                    <p :class="dm.zoneMapFallbackProse">
                        Не удалось загрузить карту. Обновите страницу или проверьте
                        <code class="text-xs">YANDEX_MAPS_API_KEY</code>.
                    </p>
                </div>
                <div
                    v-else
                    ref="mapContainerRef"
                    :class="dm.zoneMapCanvas"
                    role="img"
                    aria-label="Карта зоны доставки"
                />
            </div>
            <div
                v-else-if="mapDisplayMode === 'widget'"
                :class="dm.zoneMapLayer"
            >
                <iframe
                    :src="mapUrl"
                    title="Карта — адрес кухни"
                    :class="dm.zoneMapCanvas"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                />
            </div>
            <div
                v-else-if="mapDisplayMode === 'loading'"
                :class="dm.zoneMapLoading"
            >
                Загрузка…
            </div>
            <div
                v-else
                :class="dm.zoneMapFallback"
            >
                <p :class="dm.zoneMapFallbackProse">
                    Карта недоступна без адреса кухни в настройках.
                </p>
            </div>
        </div>

        <p
            v-if="showZonePolygonHint"
            :class="dm.zoneMapHint"
        >
            Зона на карте не отображается — проверьте полигон в админке.
        </p>
    </SecondaryContentBlock>
</template>
