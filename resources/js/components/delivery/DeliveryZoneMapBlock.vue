<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { storeToRefs } from "pinia";
import { useContentStore } from "../../modules/content/store";
import { formatCompanyAddressLine } from "../../modules/content/application/company";
import { useDeliveryZoneReadonlyMap } from "../../modules/content/application/maps";

const dm = useAppDesign().components.pages.delivery;

const contentStore = useContentStore();
const { deliveryFacts: facts, loading: deliveryLoading } = storeToRefs(contentStore);

const {
    mapUrl,
    mapDisplayMode,
    mapContainerRef,
    zoneMapMountFailed,
    showZonePolygonHint,
    showKitchenPlacemarkHint,
} = useDeliveryZoneReadonlyMap({
    facts,
    deliveryLoading,
});

const kitchenAddress = computed(() => formatCompanyAddressLine(facts.value));
const hasKitchenAddress = computed(() => kitchenAddress.value.trim() !== "");
</script>

<template>
    <SecondaryContentBlock
        title="Зона доставки и самовывоз"
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
                    aria-label="Карта зоны доставки и адреса самовывоза"
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

        <div
            v-if="hasKitchenAddress"
            :class="dm.pickupCaption"
        >
            <p :class="dm.pickupCaptionNote">
                Забрать заказ можно по адресу
            </p>
            <p :class="dm.pickupCaptionAddress">
                {{ kitchenAddress }}
            </p>
        </div>

        <p
            v-if="showZonePolygonHint"
            :class="dm.zoneMapHint"
        >
            Зона на карте не отображается — проверьте полигон в админке.
        </p>
        <p
            v-if="showKitchenPlacemarkHint"
            :class="dm.zoneMapHint"
        >
            Точка самовывоза не отображается — проверьте координаты кухни в админке.
        </p>
    </SecondaryContentBlock>
</template>
