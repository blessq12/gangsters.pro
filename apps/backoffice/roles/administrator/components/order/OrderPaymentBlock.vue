<script setup>
import { computed, ref, watch } from "vue";
import OrderSchedulePicker from "../../../../shell/components/order/OrderSchedulePicker.vue";
import UiCard from "../../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";

const props = defineProps({
    paymentMethod: { type: String, default: "cash" },
    scheduledAt: { type: String, default: "" },
    paymentError: { type: String, default: "" },
    scheduleError: { type: String, default: "" },
    readonly: { type: Boolean, default: false },
});

const emit = defineEmits(["update:paymentMethod", "update:scheduledAt"]);

const hasSlot = computed(() => String(props.scheduledAt || "").trim() !== "");

/** asap | scheduled — UI-режим; scheduled активен при слоте или после клика по дропдауну */
const timingMode = ref(hasSlot.value ? "scheduled" : "asap");

watch(
    () => props.scheduledAt,
    (value) => {
        if (String(value || "").trim()) {
            timingMode.value = "scheduled";
        }
    },
);

function selectAsap() {
    timingMode.value = "asap";
    emit("update:scheduledAt", "");
}

/** Клик по кнопке предзаказа — только режим UI; слот пишет пикер после выбора */
function onPreorderOpen() {
    timingMode.value = "scheduled";
}

function onScheduleUpdate(value) {
    timingMode.value = "scheduled";
    emit("update:scheduledAt", value);
}

function paymentLabel(method) {
    if (method === "card_courier") {
        return "Карта курьеру";
    }
    if (method === "card_online") {
        return "Карта онлайн";
    }
    return "Наличные";
}

function formatSlot(value) {
    const raw = String(value || "").trim();
    if (!raw) {
        return "Ближайшее время";
    }
    const date = new Date(raw);
    if (Number.isNaN(date.getTime())) {
        return raw;
    }
    return date.toLocaleString("ru-RU", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

const asapActive = computed(() => timingMode.value === "asap");
const preorderActive = computed(() => timingMode.value === "scheduled");
</script>

<template>
    <UiCard title="Оплата и время">
        <dl
            v-if="readonly"
            class="grid gap-6 text-sm md:grid-cols-2"
        >
            <dd class="text-bo-fg">
                {{ paymentLabel(paymentMethod) }}
            </dd>
            <dd class="text-bo-fg">
                {{ formatSlot(scheduledAt) }}
            </dd>
        </dl>

        <div
            v-else
            class="grid gap-6 md:grid-cols-2 md:gap-8"
        >
            <UiFormField :error="paymentError">
                <select
                    class="h-11 w-full border border-bo-border bg-bo-field px-3 text-sm text-bo-fg transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    :value="paymentMethod"
                    aria-label="Способ оплаты"
                    @change="$emit('update:paymentMethod', $event.target.value)"
                >
                    <option value="cash">
                        Наличные
                    </option>
                    <option value="card_courier">
                        Карта курьеру
                    </option>
                    <option value="card_online">
                        Карта онлайн
                    </option>
                </select>
            </UiFormField>

            <UiFormField :error="scheduleError">
                <div
                    class="flex w-full"
                    role="group"
                    aria-label="Режим времени"
                >
                    <button
                        type="button"
                        class="h-11 flex-1 border text-sm font-medium transition-colors"
                        :class="asapActive
                            ? 'border-bo-accent bg-bo-accent/15 text-bo-fg'
                            : 'border-bo-border bg-bo-field text-bo-muted hover:border-bo-border-strong hover:text-bo-fg'"
                        :aria-pressed="asapActive"
                        @click="selectAsap"
                    >
                        Ближайшее
                    </button>
                    <div class="-ml-px min-w-0 flex-1">
                        <OrderSchedulePicker
                            :model-value="scheduledAt"
                            :active="preorderActive"
                            empty-label="Предзаказ · выбрать"
                            @update:model-value="onScheduleUpdate"
                            @open="onPreorderOpen"
                        />
                    </div>
                </div>
            </UiFormField>
        </div>
    </UiCard>
</template>
