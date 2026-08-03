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
import { useContentStore } from "../../stores/contentStore";
import { getCurrentDayKey } from "../../utils/system/companyOpenStatus";
import {
    formatTodayWorkScheduleLine,
    getWorkScheduleRows,
    safeTrim,
} from "../../utils/system/companyDisplay";
import { useAppDesign } from "../../design/useAppDesign";

const TOOLTIP_PAD = 12;
const PANEL_MAX_WIDTH_PX = 20 * 16;

const contentStore = useContentStore();
const ws = useAppDesign().components.workSchedule;

const props = defineProps({
    layout: {
        type: String,
        default: "belowNav",
        validator: (v) => v === "belowNav" || v === "navbarCompact",
    },
});

const isNavbarCompact = computed(() => props.layout === "navbarCompact");

const stripRootClass = computed(() =>
    isNavbarCompact.value ? ws.navbarCompactRoot : ws.stripRoot,
);

const { openNow, statusHint } = useCompanyOpenStatus(
    () => contentStore.profile,
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

const hasCompany = computed(() => contentStore.profile != null);
const isLoading = computed(
    () => contentStore.loading && !contentStore.profile,
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
        contentStore.profile,
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

const triggerTitle = computed(() => {
    if (isLoading.value) return "Загрузка расписания…";
    if (!hasCompany.value) return "Расписание недоступно";
    return `${titleAttr.value}. ${summaryLine.value}`;
});

const dotClass = computed(() => {
    const d = ws.dot;
    if (!hasCompany.value) {
        return isLoading.value ? d.loading : d.noCompany;
    }
    return openNow.value ? d.open : d.closed;
});

const currentDayKey = computed(() => getCurrentDayKey(new Date()));

const scheduleRows = computed(() => {
    const c = contentStore.profile;
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
        ? formatTodayWorkScheduleLine(contentStore.profile, new Date())
        : "",
);

function isScheduleToday(dayKey) {
    return dayKey != null && dayKey === currentDayKey.value;
}

const scheduleTheme = computed(() => ws.theme);

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
    nextTick(() => {
        if (!isNavbarCompact.value && stripEnterRef.value) {
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
        :class="stripRootClass"
    >
        <template v-if="!isNavbarCompact">
            <div :class="ws.outerRow">
                <button
                    ref="triggerRef"
                    type="button"
                    :class="[ws.triggerStatic, scheduleTheme.barSurface]"
                    :disabled="!hasCompany"
                    :title="titleAttr"
                    :aria-label="ariaLabel"
                    aria-haspopup="dialog"
                    :aria-expanded="expanded"
                    @click.stop="toggleExpanded"
                >
                    <span
                        :class="[ws.dotSize, dotClass]"
                        role="presentation"
                    />
                    <span
                        :class="[ws.summaryTruncate, scheduleTheme.summaryLine]"
                    >
                        {{ summaryLine }}
                    </span>
                    <span
                        :class="[ws.accentToggleHidden, scheduleTheme.panelAccent]"
                    >
                        {{ expanded ? "Свернуть" : "Неделя" }}
                    </span>
                    <i
                        :class="[ws.chevronIcon, { 'rotate-180': expanded }]"
                        aria-hidden="true"
                    />
                </button>
            </div>
        </template>
        <template v-else>
            <button
                ref="triggerRef"
                type="button"
                :class="[ws.triggerNavbarCompact, scheduleTheme.barSurface]"
                :disabled="!hasCompany"
                :title="triggerTitle"
                :aria-label="ariaLabel"
                aria-haspopup="dialog"
                :aria-expanded="expanded"
                @click.stop="toggleExpanded"
            >
                <span
                    :class="[ws.dotSize, dotClass]"
                    role="presentation"
                />
                <i
                    class="mdi mdi-clock-outline shrink-0 text-base text-app-canvas-fg"
                    aria-hidden="true"
                />
            </button>
        </template>

        <Teleport to="body">
            <div
                v-if="expanded && hasCompany"
                ref="panelRef"
                :class="[ws.panelStatic, scheduleTheme.panelSurface]"
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
                    :class="[
                        ws.panelParagraphToday,
                        scheduleTheme.panelHeaderBorder,
                        scheduleTheme.panelMutedText,
                    ]"
                >
                    {{ todayLine }}
                </p>
                <p
                    v-else-if="scheduleRows.length && !scheduleRows[0]?.isFallbackString"
                    :class="[ws.panelParagraphByDays, scheduleTheme.panelMutedText]"
                >
                    По дням
                </p>

                <p
                    v-if="scheduleRows.length && scheduleRows[0].isFallbackString"
                    :class="[
                        ws.panelFallbackParagraph,
                        scheduleTheme.panelPrimaryText,
                    ]"
                >
                    {{ scheduleRows[0].work }}
                </p>

                <ul
                    v-else-if="scheduleRows.length"
                    :class="[ws.ulSchedule, scheduleTheme.panelDivide]"
                >
                    <li
                        v-for="(row, idx) in scheduleRows"
                        :key="row.dayKey || `row-${idx}`"
                        :class="ws.liScheduleRow"
                    >
                        <span
                            :class="[
                                ws.dayCell,
                                isScheduleToday(row.dayKey)
                                    ? scheduleTheme.todayDayLabel
                                    : scheduleTheme.panelMutedText,
                            ]"
                        >
                            {{ row.dayLabel }}
                            <span
                                v-if="isScheduleToday(row.dayKey)"
                                :class="ws.srOnlyToday"
                            > (сегодня)</span>
                        </span>
                        <span
                            :class="[
                                ws.workCell,
                                scheduleTheme.panelPrimaryText,
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
                    :class="[ws.emptyState, scheduleTheme.panelMutedText]"
                >
                    Нет данных о графике.
                </p>
            </div>
        </Teleport>
    </div>
</template>
