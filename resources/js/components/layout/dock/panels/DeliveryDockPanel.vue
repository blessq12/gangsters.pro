<script setup>
import { computed, onMounted } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useSystemStore } from "../../../../stores/systemStore";
import {
    buildDefinedConditionRows,
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

const conditionRows = computed(() => {
    if (isLoadingCompany.value) {
        return [];
    }
    return buildDefinedConditionRows(company.value);
});

onMounted(() => {
    if (!company.value && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }
});
</script>

<template>
    <div :class="d.root">
        <div :class="d.decorLayer">
            <div :class="d.decorBlobLeft"></div>
            <div :class="d.decorBlobRight"></div>
        </div>

        <div :class="d.contentColumn">
            <header :class="d.header">
                <p :class="d.kickerChip">
                    Доставка Gangsters
                </p>
                <h2 :class="d.headline">
                    Оплата и доставка
                </h2>
                <p
                    v-if="isLoadingCompany"
                    :class="d.loadingLine"
                >
                    Загрузка данных…
                </p>

                <div
                    v-if="showStatsRow"
                    :class="d.statsSection"
                >
                    <div
                        v-if="isLoadingCompany"
                        :class="d.statsRowFlex"
                    >
                        <div :class="d.statSkeletonCard">
                            <p :class="d.statLabel">
                                Данные
                            </p>
                            <p :class="d.statValueSkeleton">
                                …
                            </p>
                        </div>
                    </div>
                    <div
                        v-else
                        :class="d.statsRowFlex"
                    >
                        <div
                            v-for="stat in dockStats"
                            :key="stat.label"
                            :class="d.statCard"
                        >
                            <p :class="d.statLabel">
                                {{ stat.label }}
                            </p>
                            <p :class="d.statValue">
                                {{ stat.value }}
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            <div
                v-if="mapUrl"
                :class="d.mapFrame"
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
                v-else-if="!isLoadingCompany"
                :class="d.mapFallbackDashed"
            >
                <p :class="d.mapFallbackProse">
                    Карта недоступна без адреса кухни в настройках.
                </p>
            </div>
            <div
                v-else
                :class="d.mapLoadingBox"
            >
                Загрузка…
            </div>

            <section
                v-if="conditionRows.length > 0"
                :class="d.conditionsSection"
            >
                <h3 :class="d.conditionsHeading">
                    Условия
                </h3>
                <dl :class="d.conditionsDl">
                    <div
                        v-for="row in conditionRows"
                        :key="row.label"
                        :class="d.conditionCard"
                    >
                        <dt :class="d.conditionDt">
                            {{ row.label }}
                        </dt>
                        <dd :class="d.conditionDd">
                            {{ row.value }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</template>

<style scoped>
.wrap-break-word {
    overflow-wrap: anywhere;
    word-break: break-word;
}
</style>
