<script setup>
import { computed, ref } from "vue";
import { VueDatePicker } from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import ShellIcon from "./ShellIcon.vue";

const props = defineProps({
    /** YYYY-MM-DD */
    from: { type: String, default: "" },
    /** YYYY-MM-DD */
    to: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    emptyLabel: { type: String, default: "Период" },
});

const emit = defineEmits(["update:from", "update:to"]);

/** @type {import('vue').Ref<InstanceType<typeof VueDatePicker> | null>} */
const pickerRef = ref(null);
const menuOpen = ref(false);

const timeConfig = {
    enableTimePicker: false,
};

/** partialRange: false — ждать второй клик (до), иначе auto-apply закрывает после «от». */
const rangeConfig = {
    partialRange: false,
};

const formats = {
    input: "dd.MM.yyyy",
};

const inputAttrs = {
    clearable: false,
    hideInputIcon: true,
};

/**
 * @param {number} n
 */
function pad(n) {
    return String(n).padStart(2, "0");
}

/**
 * @param {Date} date
 * @returns {string}
 */
function toDateString(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

/**
 * @param {string} raw
 * @returns {Date|null}
 */
function parseDay(raw) {
    const value = String(raw || "").trim();
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return null;
    }
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
}

/**
 * @param {Date} date
 */
function formatLabel(date) {
    return date.toLocaleDateString("ru-RU", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

const hasValue = computed(() => parseDay(props.from) !== null && parseDay(props.to) !== null);

const buttonLabel = computed(() => {
    const fromDate = parseDay(props.from);
    const toDate = parseDay(props.to);
    if (!fromDate || !toDate) {
        return props.emptyLabel;
    }
    const fromLabel = formatLabel(fromDate);
    const toLabel = formatLabel(toDate);
    if (fromLabel === toLabel) {
        return fromLabel;
    }
    return `${fromLabel} — ${toLabel}`;
});

const pickerValue = computed({
    get: () => {
        const fromDate = parseDay(props.from);
        const toDate = parseDay(props.to);
        if (!fromDate || !toDate) {
            return null;
        }
        return [fromDate, toDate];
    },
    set: (value) => {
        if (!Array.isArray(value) || value.length < 2 || !value[0] || !value[1]) {
            emit("update:from", "");
            emit("update:to", "");
            return;
        }
        const start = value[0] instanceof Date ? value[0] : new Date(value[0]);
        const end = value[1] instanceof Date ? value[1] : new Date(value[1]);
        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
            emit("update:from", "");
            emit("update:to", "");
            return;
        }
        emit("update:from", toDateString(start));
        emit("update:to", toDateString(end));
    },
});

function onMenuOpen() {
    menuOpen.value = true;
}

function onMenuClosed() {
    menuOpen.value = false;
}

function onTriggerClick() {
    if (props.disabled) {
        return;
    }
    pickerRef.value?.toggleMenu();
}
</script>

<template>
    <VueDatePicker
        ref="pickerRef"
        v-model="pickerValue"
        :week-start="1"
        dark
        :range="rangeConfig"
        :time-config="timeConfig"
        :formats="formats"
        :disabled="disabled"
        :input-attrs="inputAttrs"
        auto-apply
        teleport="body"
        :ui="{ menu: 'period-range-menu' }"
        class="period-range-picker"
        @open="onMenuOpen"
        @closed="onMenuClosed"
    >
        <template #trigger>
            <button
                type="button"
                class="flex h-11 w-full min-w-[12rem] items-center gap-2 border px-3 text-left text-sm font-medium transition-colors"
                :class="
                    menuOpen || hasValue
                        ? 'border-bo-accent bg-bo-accent/15 text-bo-fg'
                        : 'border-bo-border bg-bo-field text-bo-muted hover:border-bo-border-strong hover:text-bo-fg'
                "
                :disabled="disabled"
                :aria-expanded="menuOpen ? 'true' : 'false'"
                aria-haspopup="dialog"
                aria-label="Выбрать период"
                @click.stop="onTriggerClick"
            >
                <ShellIcon name="calendar" class="h-4 w-4 shrink-0 opacity-80" />
                <span class="min-w-0 flex-1 truncate" :class="hasValue ? 'text-bo-fg' : ''">
                    {{ buttonLabel }}
                </span>
                <ShellIcon
                    name="chevron-down"
                    class="h-4 w-4 shrink-0 opacity-70 transition-transform"
                    :class="menuOpen ? 'rotate-180' : ''"
                />
            </button>
        </template>
    </VueDatePicker>
</template>

<style>
.period-range-picker {
    width: auto;
}

.period-range-menu.dp__theme_dark,
.dp__menu.period-range-menu {
    --dp-background-color: var(--bo-elevated, #252525);
    --dp-text-color: var(--bo-fg, #ececec);
    --dp-hover-color: rgba(255, 255, 255, 0.08);
    --dp-hover-text-color: var(--bo-fg, #ececec);
    --dp-primary-color: var(--bo-accent, #c62424);
    --dp-primary-text-color: #fff;
    --dp-secondary-color: var(--bo-muted, #a3a3a3);
    --dp-border-color: var(--bo-border, rgba(255, 255, 255, 0.08));
    --dp-menu-border-color: var(--bo-border, rgba(255, 255, 255, 0.08));
    --dp-input-icon-color: var(--bo-muted, #a3a3a3);
}
</style>
