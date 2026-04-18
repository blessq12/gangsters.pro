<script setup>
import { computed, onMounted } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useSystemStore } from "../../stores/systemStore";
import {
    buildCheckoutAlignedPaymentInfoBlocks,
    buildDeliveryHeroStats,
    buildYandexMapWidgetSearchUrl,
    formatAverageDeliveryLine,
    formatCoverageLine,
    formatDeliveryFeeRublesLine,
    formatMinOrderRublesLine,
    kitchenAddressLine,
} from "../../utils/system/companyDeliveryFacts";

const userStore = useUserStore();
const systemStore = useSystemStore();

const company = computed(() => systemStore.company);

const selectedAddressLabel = computed(() => {
    if (userStore.selectedAddress?.label) {
        return userStore.selectedAddress.label;
    }
    return "Адрес не выбран";
});

const paymentMethodLabels = computed(() =>
    buildCheckoutAlignedPaymentInfoBlocks().map((b) => b.title),
);

const kitchenLine = computed(() => kitchenAddressLine(company.value));

const mapUrl = computed(() => buildYandexMapWidgetSearchUrl(company.value));

const dockStats = computed(() => {
    if (systemStore.loadingCompany && !company.value) {
        return [
            { label: "Срок", value: "…" },
            { label: "Мин. заказ", value: "…" },
            { label: "Покрытие", value: "…" },
        ];
    }
    return buildDeliveryHeroStats(company.value);
});

const timeLine = computed(() => {
    if (systemStore.loadingCompany && !company.value) return "…";
    return formatAverageDeliveryLine(company.value);
});

const coverageShort = computed(() => {
    if (systemStore.loadingCompany && !company.value) return "…";
    const line = formatCoverageLine(company.value);
    return line !== "—" ? line : "уточняется при заказе";
});

onMounted(() => {
    if (!company.value && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }
});
</script>

<template>
    <div
        class="overflow-hidden rounded-[2rem] border border-amber-400/30 bg-[rgba(0,0,0,0.9)] px-4 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur sm:px-6 lg:px-8"
    >
        <div class="pointer-events-none absolute inset-0 opacity-40 mix-blend-screen">
            <div
                class="absolute -left-10 top-0 h-40 w-40 rounded-full bg-amber-500/10 blur-3xl"
            ></div>
            <div
                class="absolute -right-8 bottom-0 h-48 w-48 rounded-full bg-rose-500/10 blur-3xl"
            ></div>
        </div>

        <div class="relative flex flex-col gap-4 text-xs text-slate-200 sm:text-sm">
            <div
                class="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.02))] px-4 py-4 sm:px-5"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="max-w-xl">
                        <p
                            class="mb-2 inline-flex rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-[11px] uppercase tracking-[0.24em] text-amber-200"
                        >
                            Доставка Gangsters
                        </p>
                        <p class="text-lg font-semibold text-slate-50 sm:text-xl">
                            Оплата и доставка
                        </p>
                    </div>

                    <div class="hidden grid-cols-3 gap-2 sm:grid sm:gap-3">
                        <div
                            v-for="s in dockStats"
                            :key="s.label"
                            class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 backdrop-blur"
                        >
                            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">
                                {{ s.label }}
                            </p>
                            <p class="mt-1 font-semibold text-amber-300">
                                {{ s.value }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="mapUrl"
                class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-black/30 shadow-[0_18px_50px_rgba(0,0,0,0.45)]"
            >
                <iframe
                    :src="mapUrl"
                    title="Карта — адрес кухни"
                    class="h-56 w-full border-0 sm:h-64 lg:h-72 xl:h-80"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                />
            </div>
            <div
                v-else
                class="flex h-56 items-center justify-center rounded-[1.75rem] border border-dashed border-white/15 bg-black/25 px-4 text-center text-sm text-slate-400 sm:h-64 lg:h-72 xl:h-80"
            >
                <span v-if="systemStore.loadingCompany">Загрузка карты…</span>
                <span v-else>Адрес для карты уточняется.</span>
            </div>

            <div
                class="grid gap-3 lg:gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.9fr)] xl:grid-cols-[minmax(0,1fr)_minmax(320px,0.9fr)]"
            >
                <div
                    class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-4 sm:px-5"
                >
                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                        Адрес доставки
                    </p>
                    <p class="mt-2 text-sm font-semibold text-slate-100 sm:text-base">
                        {{ selectedAddressLabel }}
                    </p>
                    <p class="mt-2 text-slate-400">
                        <span v-if="kitchenLine">Кухня: {{ kitchenLine }}</span>
                        <span v-else-if="systemStore.loadingCompany">Кухня: загрузка…</span>
                        <span v-else>Кухня: адрес уточняется</span>
                    </p>
                    <div class="mt-4 hidden gap-2 sm:grid sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">
                                Время
                            </p>
                            <p class="mt-1 text-sm text-slate-200">
                                {{ timeLine }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">
                                Покрытие
                            </p>
                            <p class="mt-1 text-sm text-slate-200">
                                {{ coverageShort }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="hidden gap-3 sm:grid lg:gap-4">
                    <div
                        class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-4"
                    >
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                            Условия
                        </p>
                        <div class="mt-3 space-y-2 text-slate-300">
                            <div class="flex items-center justify-between gap-3">
                                <span>Мин. заказ</span>
                                <span class="font-semibold text-amber-300">{{
                                    systemStore.loadingCompany && !company
                                        ? "…"
                                        : formatMinOrderRublesLine(company)
                                }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Доставка от</span>
                                <span class="font-semibold text-slate-100">{{
                                    systemStore.loadingCompany && !company
                                        ? "…"
                                        : formatDeliveryFeeRublesLine(company)
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-[1.75rem] border border-white/10 bg-[rgba(255,255,255,0.03)] px-4 py-4"
                    >
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">
                            Способы оплаты
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span
                                v-for="method in paymentMethodLabels"
                                :key="method"
                                class="rounded-full bg-black/70 px-3 py-1 text-[11px] text-slate-100"
                            >
                                {{ method }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
