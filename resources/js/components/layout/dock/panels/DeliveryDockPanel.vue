<script setup>
import { computed, onMounted } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useSystemStore } from "../../../../stores/systemStore";
import {
    buildDefinedDeliveryStats,
    buildYandexMapWidgetSearchUrl,
} from "../../../../utils/system/companyDeliveryFacts";

const panels = useAppDesign().components.dockPanels;
const d = panels.delivery;

const systemStore = useSystemStore();

const company = computed(() => systemStore.company);

const mapUrl = computed(() => buildYandexMapWidgetSearchUrl(company.value));

const isLoadingCompany = computed(
    () => systemStore.loadingCompany && !company.value,
);

const dockStats = computed(() => {
    if (isLoadingCompany.value) {
        return [];
    }
    return buildDefinedDeliveryStats(company.value);
});

const showStatsRow = computed(
    () => dockStats.value.length > 0 || isLoadingCompany.value,
);

onMounted(() => {
    if (!company.value && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }
});
</script>

<template>
    <DockPanelLayout
        title="Оплата и доставка"
        :body-class="d.mapBodyOverride"
    >
        <div :class="d.mapStage">
            <div
                v-if="mapUrl"
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
                v-else-if="isLoadingCompany"
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
                    v-if="isLoadingCompany"
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
