<script setup>
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useContentStore } from "../../stores/contentStore";
import { useKitchenAddressReadonlyMap } from "../../features/delivery/useKitchenAddressReadonlyMap";

const cm = useAppDesign().components.pages.contacts;

const contentStore = useContentStore();
const { deliveryFacts: facts, loading: deliveryLoading } = storeToRefs(contentStore);

const {
    mapUrl,
    mapDisplayMode,
    mapContainerRef,
    mapMountFailed,
    showPlacemarkHint,
} = useKitchenAddressReadonlyMap({
    facts,
    deliveryLoading,
});
</script>

<template>
    <div :class="cm.kitchenMapStage">
        <div
            v-if="mapDisplayMode === 'zone-sdk'"
            :class="cm.kitchenMapLayer"
        >
            <div
                v-if="mapMountFailed"
                :class="cm.kitchenMapFallback"
            >
                <p :class="cm.kitchenMapFallbackProse">
                    Не удалось загрузить карту. Обновите страницу или проверьте
                    <code class="text-xs">YANDEX_MAPS_API_KEY</code>.
                </p>
            </div>
            <div
                v-else
                ref="mapContainerRef"
                :class="cm.kitchenMapCanvas"
                role="img"
                aria-label="Карта адреса кухни"
            />
        </div>
        <div
            v-else-if="mapDisplayMode === 'widget'"
            :class="cm.kitchenMapLayer"
        >
            <iframe
                :src="mapUrl"
                title="Карта — адрес кухни"
                :class="cm.kitchenMapCanvas"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            />
        </div>
        <div
            v-else-if="mapDisplayMode === 'loading'"
            :class="cm.kitchenMapLoading"
        >
            Загрузка…
        </div>
        <div
            v-else
            :class="cm.kitchenMapFallback"
        >
            <p :class="cm.kitchenMapFallbackProse">
                Карта недоступна без адреса кухни в настройках.
            </p>
        </div>
    </div>

    <p
        v-if="showPlacemarkHint"
        :class="cm.kitchenMapHint"
    >
        Точка на карте не отображается — проверьте координаты кухни в админке.
    </p>
</template>
