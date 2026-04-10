<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
    watch,
} from "vue";
import {
    playTooltipClose,
    playTooltipOpen,
    playWorkScheduleStripEnter,
} from "../../animations/animationManager";
import { useCompanyOpenStatus } from "../../composables/system/useCompanyOpenStatus";
import { useSystemStore } from "../../stores/systemStore";
import { useThemeStore } from "../../stores/themeStore";
import { getCurrentDayKey } from "../../utils/system/companyOpenStatus";
import {
    formatTodayWorkScheduleLine,
    getWorkScheduleRows,
    safeTrim,
} from "../../utils/system/companyDisplay";

const TOOLTIP_PAD = 12;
const PANEL_MAX_WIDTH_PX = 20 * 16;

const systemStore = useSystemStore();
const themeStore = useThemeStore();

const { openNow, statusHint } = useCompanyOpenStatus(
    () => systemStore.company,
);

const stripEnterRef = ref(null);
const triggerRef = ref(null);
const panelRef = ref(null);
const expanded = ref(false);

const panelPos = ref({
    top: "0px",
    left: "0px",
    width: "min(20rem, calc(100vw - 2rem))",
    maxHeight: "70vh",
});

const hasCompany = computed(() => systemStore.company != null);
const isLoading = computed(
    () => systemStore.loadingCompany && !systemStore.company,
);

const openLabel = computed(() => {
    if (!hasCompany.value) return "";
    return openNow.value ? "Открыто" : "Закрыто";
});

const secondaryLine = computed(() => {
    if (!hasCompany.value) return "";
    return statusHint.value?.hint || "";
});

const summaryLine = computed(() => {
    if (isLoading.value) return "Загрузка расписания…";
    if (!hasCompany.value) return "Расписание недоступно";
    const hint = secondaryLine.value;
    const base = openLabel.value;
    if (hint) return `${base} · ${hint}`;
    const today = formatTodayWorkScheduleLine(
        systemStore.company,
        new Date(),
    );
    return today || base;
});

const titleAttr = computed(() => {
    if (isLoading.value) return "Загрузка расписания…";
    if (!hasCompany.value) return "Расписание недоступно";
    return expanded.value
        ? "Скрыть расписание на неделю"
        : "Показать расписание на неделю";
});

const ariaLabel = computed(() => titleAttr.value);

const dotClass = computed(() => {
    if (!hasCompany.value) {
        return isLoading.value
            ? "bg-slate-400/90 shadow-none animate-pulse"
            : "bg-slate-500 shadow-none";
    }
    return openNow.value
        ? "bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.7)]"
        : "bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.7)]";
});

const currentDayKey = computed(() => getCurrentDayKey(new Date()));

const scheduleRows = computed(() => {
    const c = systemStore.company;
    if (!c) return [];
    const rows = getWorkScheduleRows(c.work_schedule);
    if (rows.length) return rows;
    const wh = safeTrim(c.work_hours);
    if (wh) {
        return [
            {
                dayKey: null,
                dayLabel: "",
                isDayOff: false,
                work: wh,
                isFallbackString: true,
            },
        ];
    }
    return [];
});

const todayLine = computed(() =>
    hasCompany.value
        ? formatTodayWorkScheduleLine(systemStore.company, new Date())
        : "",
);

function isScheduleToday(dayKey) {
    return dayKey != null && dayKey === currentDayKey.value;
}

const barSurfaceClass = computed(() =>
    themeStore.theme === "light"
        ? "border-slate-200/90 bg-white/80 shadow-md hover:bg-white/95"
        : "border-white/10 bg-[rgba(255,255,255,0.06)] shadow-[0_0_20px_rgba(0,0,0,0.45)] hover:bg-[rgba(255,255,255,0.09)]",
);

const panelSurfaceClass = computed(() =>
    themeStore.theme === "light"
        ? "border-slate-200/60 bg-white shadow-md"
        : "border-white/[0.08] bg-[#1f1f23]/95 shadow-xl",
);

const panelDivideClass = computed(() =>
    themeStore.theme === "light"
        ? "divide-slate-200/70"
        : "divide-white/[0.08]",
);

const panelHeaderBorderClass = computed(() =>
    themeStore.theme === "light"
        ? "border-b border-slate-200/70"
        : "border-b border-white/[0.08]",
);

const panelPrimaryTextClass = computed(() =>
    themeStore.theme === "light" ? "text-slate-800" : "text-slate-200",
);

const panelMutedTextClass = computed(() =>
    themeStore.theme === "light" ? "text-slate-600" : "text-slate-400",
);

const panelAccentClass = computed(() =>
    themeStore.theme === "light" ? "text-amber-600/95" : "text-amber-400/90",
);

function panelWidthPx() {
    return Math.min(PANEL_MAX_WIDTH_PX, window.innerWidth - TOOLTIP_PAD * 2);
}

function updatePanelPosition() {
    if (!triggerRef.value || !expanded.value) return;
    const rect = triggerRef.value.getBoundingClientRect();
    const w = panelWidthPx();
    let left = rect.left + rect.width / 2 - w / 2;
    left = Math.max(
        TOOLTIP_PAD,
        Math.min(left, window.innerWidth - w - TOOLTIP_PAD),
    );
    const top = rect.bottom + TOOLTIP_PAD;
    const maxH = Math.max(
        120,
        Math.min(
            window.innerHeight * 0.7,
            window.innerHeight - top - TOOLTIP_PAD,
        ),
    );
    panelPos.value = {
        top: `${top}px`,
        left: `${left}px`,
        width: `${w}px`,
        maxHeight: `${maxH}px`,
    };
}

function closeExpanded() {
    if (!expanded.value) return;
    playTooltipClose(panelRef.value, () => {
        expanded.value = false;
    });
}

function openExpanded() {
    if (expanded.value) return;
    expanded.value = true;
    nextTick(() => {
        updatePanelPosition();
        playTooltipOpen(panelRef.value);
    });
}

function toggleExpanded() {
    if (!hasCompany.value) return;
    if (expanded.value) {
        closeExpanded();
    } else {
        openExpanded();
    }
}

function onViewportChange() {
    if (expanded.value) {
        updatePanelPosition();
    }
}

let docClickHandler = null;
let keydownHandler = null;

watch(expanded, (open) => {
    if (open) {
        nextTick(() => updatePanelPosition());
    }
});

onMounted(() => {
    if (!systemStore.company && !systemStore.loadingCompany) {
        void systemStore.fetchCompany();
    }

    nextTick(() => {
        if (stripEnterRef.value) {
            playWorkScheduleStripEnter(stripEnterRef.value);
        }
    });

    docClickHandler = (e) => {
        if (!expanded.value) return;
        if (triggerRef.value?.contains(e.target)) return;
        if (panelRef.value?.contains(e.target)) return;
        closeExpanded();
    };
    document.addEventListener("click", docClickHandler);

    keydownHandler = (e) => {
        if (e.key === "Escape") closeExpanded();
    };
    document.addEventListener("keydown", keydownHandler);

    window.addEventListener("scroll", onViewportChange, { passive: true });
    window.addEventListener("resize", onViewportChange);
});

onUnmounted(() => {
    if (docClickHandler) {
        document.removeEventListener("click", docClickHandler);
    }
    if (keydownHandler) {
        document.removeEventListener("keydown", keydownHandler);
    }
    window.removeEventListener("scroll", onViewportChange);
    window.removeEventListener("resize", onViewportChange);
});
</script>

<template>
    <div
        ref="stripEnterRef"
        class="w-full opacity-0"
    >
        <div
            class="mx-auto mt-3 flex max-w-7xl justify-center px-4 pt-2 sm:mt-4 sm:px-6 md:max-w-none lg:px-8"
        >
            <button
                ref="triggerRef"
                type="button"
                class="inline-flex w-max max-w-[min(90vw,24rem)] shrink-0 items-center gap-2 rounded-2xl border px-3 py-2 text-left text-sm outline-none backdrop-blur-sm transition-colors focus-visible:ring-2 focus-visible:ring-amber-400/60 disabled:cursor-not-allowed disabled:opacity-60 md:max-w-[min(90vw,28rem)] sm:px-4"
                :class="barSurfaceClass"
                :disabled="!hasCompany"
                :title="titleAttr"
                :aria-label="ariaLabel"
                aria-haspopup="dialog"
                :aria-expanded="expanded"
                @click.stop="toggleExpanded"
            >
                <span
                    class="h-2.5 w-2.5 shrink-0 rounded-full sm:h-3 sm:w-3"
                    :class="dotClass"
                    role="presentation"
                />
                <span
                    class="max-w-[min(65vw,16rem)] truncate font-medium sm:max-w-[min(50vw,18rem)] md:max-w-[20rem]"
                    :class="
                        themeStore.theme === 'light'
                            ? 'text-slate-800'
                            : 'text-slate-100'
                    "
                >
                    {{ summaryLine }}
                </span>
                <span
                    class="hidden shrink-0 text-xs font-medium sm:inline"
                    :class="panelAccentClass"
                >
                    {{ expanded ? "Свернуть" : "Неделя" }}
                </span>
                <i
                    class="mdi mdi-chevron-down shrink-0 text-lg text-slate-400 transition-transform duration-200"
                    :class="{ 'rotate-180': expanded }"
                    aria-hidden="true"
                />
            </button>
        </div>

        <Teleport to="body">
            <div
                v-if="expanded && hasCompany"
                ref="panelRef"
                class="fixed z-[80] overflow-y-auto rounded-xl border px-4 py-3 backdrop-blur-sm sm:px-5 sm:py-3.5"
                :class="panelSurfaceClass"
                :style="{
                    top: panelPos.top,
                    left: panelPos.left,
                    width: panelPos.width,
                    maxHeight: panelPos.maxHeight,
                }"
                role="dialog"
                aria-label="Расписание работы на неделю"
                @click.stop
            >
                <p
                    v-if="todayLine && !scheduleRows[0]?.isFallbackString"
                    class="mb-3 pb-2.5 text-xs leading-snug"
                    :class="[panelHeaderBorderClass, panelMutedTextClass]"
                >
                    {{ todayLine }}
                </p>
                <p
                    v-else-if="scheduleRows.length && !scheduleRows[0]?.isFallbackString"
                    class="mb-3 text-xs"
                    :class="panelMutedTextClass"
                >
                    По дням
                </p>

                <p
                    v-if="scheduleRows.length && scheduleRows[0].isFallbackString"
                    class="text-sm leading-relaxed tabular-nums"
                    :class="panelPrimaryTextClass"
                >
                    {{ scheduleRows[0].work }}
                </p>

                <ul
                    v-else-if="scheduleRows.length"
                    class="divide-y text-sm"
                    :class="panelDivideClass"
                >
                    <li
                        v-for="(row, idx) in scheduleRows"
                        :key="row.dayKey || `row-${idx}`"
                        class="flex items-baseline justify-between gap-4 py-2 first:pt-0 last:pb-0"
                    >
                        <span
                            class="w-8 shrink-0 text-xs font-medium tabular-nums"
                            :class="
                                isScheduleToday(row.dayKey)
                                    ? themeStore.theme === 'light'
                                        ? 'text-amber-700'
                                        : 'text-amber-300/90'
                                    : panelMutedTextClass
                            "
                        >
                            {{ row.dayLabel }}
                            <span
                                v-if="isScheduleToday(row.dayKey)"
                                class="sr-only"
                            > (сегодня)</span>
                        </span>
                        <span
                            class="min-w-0 text-right text-sm tabular-nums"
                            :class="[
                                panelPrimaryTextClass,
                                isScheduleToday(row.dayKey) ? 'font-medium' : '',
                            ]"
                        >
                            <template v-if="row.isDayOff">Выходной</template>
                            <template v-else>{{ row.work || "—" }}</template>
                        </span>
                    </li>
                </ul>

                <p
                    v-else
                    class="text-sm leading-relaxed"
                    :class="panelMutedTextClass"
                >
                    Нет данных о графике.
                </p>
            </div>
        </Teleport>
    </div>
</template>
