<script setup>
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useContentStore } from "../../modules/content/store";
import { useBranchAddressReadonlyMap } from "../../modules/content/application/maps";

const cm = useAppDesign().components.pages.contacts;

const contentStore = useContentStore();
const { deliveryFacts: facts, loading: deliveryLoading } = storeToRefs(contentStore);

const {
    mapUrl,
    mapDisplayMode,
    mapContainerRef,
    mapMountFailed,
    showPlacemarkHint,
} = useBranchAddressReadonlyMap({
    facts,
    deliveryLoading,
});
</script>

<template>
    <div :class="cm.branchMapStage">
        <div
            v-if="mapDisplayMode === 'zone-sdk'"
            :class="cm.branchMapLayer"
        >
            <div
                v-if="mapMountFailed"
                :class="cm.branchMapFallback"
            >
                <p :class="cm.branchMapFallbackProse">
                    Не удалось загрузить карту. Обновите страницу или проверьте
                    <code class="text-xs">YANDEX_MAPS_API_KEY</code>.
                </p>
            </div>
            <div
                v-else
                ref="mapContainerRef"
                :class="cm.branchMapCanvas"
                role="img"
                aria-label="Карта адреса кухни"
            />
        </div>
        <div
            v-else-if="mapDisplayMode === 'widget'"
            :class="cm.branchMapLayer"
        >
            <iframe
                :src="mapUrl"
                title="Карта — адрес кухни"
                :class="cm.branchMapCanvas"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            />
        </div>
        <div
            v-else-if="mapDisplayMode === 'loading'"
            :class="cm.branchMapLoading"
        >
            Загрузка…
        </div>
        <div
            v-else
            :class="cm.branchMapFallback"
        >
            <p :class="cm.branchMapFallbackProse">
                Карта недоступна без адреса кухни в настройках.
            </p>
        </div>
    </div>

    <p
        v-if="showPlacemarkHint"
        :class="cm.branchMapHint"
    >
        Точка на карте не отображается — проверьте координаты кухни в админке.
    </p>
</template>
