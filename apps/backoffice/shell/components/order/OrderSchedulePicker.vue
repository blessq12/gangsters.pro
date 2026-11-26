<script setup>
import { computed, ref } from "vue";
import { VueDatePicker } from "@vuepic/vue-datepicker";
import { ru } from "date-fns/locale";
import "@vuepic/vue-datepicker/dist/main.css";
import ShellIcon from "../ShellIcon.vue";

const props = defineProps({
    /** Store format: YYYY-MM-DDTHH:mm или пусто */
    modelValue: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    /** Текст кнопки, если слота ещё нет */
    emptyLabel: { type: String, default: "Выбрать дату и время" },
    /** Шаг минут */
    minutesIncrement: { type: Number, default: 15 },
    /** Кнопка активна (режим предзаказ) */
    active: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "open"]);

/** @type {import('vue').Ref<InstanceType<typeof VueDatePicker> | null>} */
const pickerRef = ref(null);
const menuOpen = ref(false);

const minDate = computed(() => new Date());

const timeConfig = computed(() => ({
    enableTimePicker: true,
    minutesIncrement: props.minutesIncrement,
    is24: true,
}));

const formats = {
    input: "dd.MM.yyyy HH:mm",
};

const inputAttrs = {
    clearable: false,
    hideInputIcon: true,
};

const hasValue = computed(() => {
    const raw = String(props.modelValue || "").trim();
    if (!raw) {
        return false;
    }
    const date = new Date(raw);
    return !Number.isNaN(date.getTime());
});

const buttonLabel = computed(() => {
    if (!hasValue.value) {
        return props.emptyLabel;
    }
    const date = new Date(String(props.modelValue).trim());
    return date.toLocaleString("ru-RU", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
});

/**
 * @param {Date|string|null|undefined} value
 * @returns {string}
 */
function toStoreString(value) {
    if (value == null || value === "") {
        return "";
    }
    if (typeof value === "string") {
        const trimmed = value.trim();
        if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(trimmed)) {
            return trimmed.slice(0, 16);
        }
        const parsed = new Date(trimmed);
        if (Number.isNaN(parsed.getTime())) {
            return "";
        }
        return formatLocal(parsed);
    }
    if (value instanceof Date && !Number.isNaN(value.getTime())) {
        return formatLocal(value);
    }
    return "";
}

/**
 * @param {Date} date
 */
function formatLocal(date) {
    const pad = (n) => String(n).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const pickerValue = computed({
    get: () => {
        const raw = String(props.modelValue || "").trim();
        if (!raw) {
            return null;
        }
        const date = new Date(raw);
        return Number.isNaN(date.getTime()) ? null : date;
    },
    set: (value) => {
        emit("update:modelValue", toStoreString(value));
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
    emit("open");
    pickerRef.value?.toggleMenu();
}
</script>

<template>
    <VueDatePicker
        ref="pickerRef"
        v-model="pickerValue"
        :locale="ru"
        dark
        :time-config="timeConfig"
        :formats="formats"
        :min-date="minDate"
        :disabled="disabled"
        :input-attrs="inputAttrs"
        auto-apply
        teleport="body"
        :ui="{ menu: 'order-schedule-menu' }"
        class="order-schedule-picker w-full"
        @open="onMenuOpen"
        @closed="onMenuClosed"
    >
        <template #trigger>
            <button
                type="button"
                class="flex h-11 w-full items-center gap-2 border px-3 text-left text-sm font-medium transition-colors"
                :class="active || menuOpen
                    ? 'border-bo-accent bg-bo-accent/15 text-bo-fg'
                    : 'border-bo-border bg-bo-field text-bo-muted hover:border-bo-border-strong hover:text-bo-fg'"
                :disabled="disabled"
                :aria-expanded="menuOpen ? 'true' : 'false'"
                aria-haspopup="dialog"
                aria-label="Предзаказ — открыть выбор даты и времени"
                @click.stop="onTriggerClick"
            >
                <ShellIcon
                    name="calendar"
                    class="h-4 w-4 shrink-0 opacity-80"
                />
                <span
                    class="min-w-0 flex-1 truncate"
                    :class="hasValue ? 'text-bo-fg' : ''"
                >{{ buttonLabel }}</span>
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
.order-schedule-picker {
    width: 100%;
}

.order-schedule-menu.dp__theme_dark,
.dp__menu.order-schedule-menu {
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
