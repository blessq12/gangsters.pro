<script setup>
import { computed, onMounted } from "vue";
import { useSystemStore } from "../../stores/systemStore";
import {
    buildDefinedConditionRows,
    buildDefinedDeliveryStats,
    buildYandexMapWidgetSearchUrl,
} from "../../utils/system/companyDeliveryFacts";

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
    <div
        class="w-full min-w-0 max-w-full overflow-hidden rounded-[2rem] border border-amber-400/30 bg-[rgba(0,0,0,0.9)] px-4 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur sm:px-6 lg:px-8"
    >
        <div class="pointer-events-none absolute inset-0 opacity-40 mix-blend-screen">
            <div
                class="absolute -left-10 top-0 h-40 w-40 rounded-full bg-amber-500/10 blur-3xl"
            ></div>
            <div
                class="absolute -right-8 bottom-0 h-48 w-48 rounded-full bg-rose-500/10 blur-3xl"
            ></div>
        </div>

        <div
            class="relative w-full min-w-0 space-y-4 text-xs text-slate-200 sm:text-sm"
        >
            <header
                class="w-full min-w-0 rounded-[1.75rem] border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.02))] px-4 py-4 sm:px-5"
            >
                <p
                    class="mb-2 inline-flex rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-amber-200"
                >
                    Доставка Gangsters
                </p>
                <h2 class="text-lg font-semibold text-slate-50 sm:text-xl">
                    Оплата и доставка
                </h2>
                <p
                    v-if="isLoadingCompany"
                    class="mt-2 text-sm text-slate-400"
                >
                    Загрузка данных…
                </p>

                <div
                    v-if="showStatsRow"
                    class="mt-4 w-full min-w-0 border-t border-white/10 pt-4"
                >
                    <div
                        v-if="isLoadingCompany"
                        class="flex flex-row flex-wrap gap-2"
                    >
                        <div
                            class="min-w-0 flex-1 basis-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3 sm:basis-auto"
                        >
                            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">
                                Данные
                            </p>
                            <p class="mt-1 font-semibold text-amber-300/90">
                                …
                            </p>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex flex-row flex-wrap gap-2"
                    >
                        <div
                            v-for="s in dockStats"
                            :key="s.label"
                            class="min-w-0 flex-1 basis-[7.5rem] rounded-2xl border border-white/10 bg-black/25 px-3 py-3 sm:px-4"
                        >
                            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">
                                {{ s.label }}
                            </p>
                            <p
                                class="mt-1 min-w-0 wrap-break-word text-left text-sm font-semibold leading-relaxed text-amber-300 sm:text-base"
                            >
                                {{ s.value }}
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            <div
                v-if="mapUrl"
                class="w-full min-w-0 overflow-hidden rounded-[1.75rem] border border-white/10 bg-black/30 shadow-[0_18px_50px_rgba(0,0,0,0.45)]"
            >
                <iframe
                    :src="mapUrl"
                    title="Карта — адрес кухни"
                    class="h-56 w-full min-w-0 border-0 sm:h-64 lg:h-72 xl:h-80"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                />
            </div>
            <div
                v-else-if="!isLoadingCompany"
                class="grid min-h-[14rem] w-full min-w-0 place-content-center rounded-[1.75rem] border border-dashed border-white/15 bg-black/25 px-4 py-8 text-center text-sm text-slate-500 sm:min-h-[16rem]"
            >
                <p class="mx-auto max-w-prose leading-relaxed">
                    Карта недоступна без адреса кухни в настройках.
                </p>
            </div>
            <div
                v-else
                class="grid min-h-[10rem] w-full place-content-center rounded-[1.75rem] border border-white/10 bg-black/20 text-sm text-slate-500"
            >
                Загрузка…
            </div>

            <section
                v-if="conditionRows.length > 0"
                class="w-full min-w-0 rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-4 sm:px-5"
            >
                <h3
                    class="text-[11px] font-medium uppercase tracking-[0.22em] text-slate-400"
                >
                    Условия
                </h3>
                <dl
                    class="mt-3 flex w-full min-w-0 flex-row flex-wrap gap-2"
                >
                    <div
                        v-for="row in conditionRows"
                        :key="row.label"
                        class="min-w-0 flex-1 basis-[8rem] rounded-2xl border border-white/10 bg-black/20 px-3 py-3 sm:px-4"
                    >
                        <dt class="text-[11px] uppercase tracking-[0.18em] text-slate-500">
                            {{ row.label }}
                        </dt>
                        <dd
                            class="mt-1.5 min-w-0 wrap-break-word text-pretty text-base font-semibold leading-relaxed text-amber-300"
                        >
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
