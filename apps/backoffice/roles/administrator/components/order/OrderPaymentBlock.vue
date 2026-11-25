<script setup>
import UiCard from "../../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

defineProps({
    paymentMethod: { type: String, default: "cash" },
    changeFrom: { type: String, default: "" },
    scheduledAt: { type: String, default: "" },
    paymentError: { type: String, default: "" },
    readonly: { type: Boolean, default: false },
});

defineEmits(["update:paymentMethod", "update:changeFrom", "update:scheduledAt"]);

function paymentLabel(method) {
    if (method === "card_courier") {
        return "Карта курьеру";
    }
    if (method === "card_online") {
        return "Карта онлайн";
    }
    return "Наличные";
}
</script>

<template>
    <UiCard title="Оплата и слот">
        <dl
            v-if="readonly"
            class="grid gap-3 text-sm"
        >
            <div>
                <dt class="text-xs uppercase tracking-wide text-bo-muted">Способ</dt>
                <dd class="mt-1 text-bo-fg">{{ paymentLabel(paymentMethod) }}</dd>
            </div>
            <div v-if="paymentMethod === 'cash' && changeFrom">
                <dt class="text-xs uppercase tracking-wide text-bo-muted">Сдача с</dt>
                <dd class="mt-1 text-bo-fg">{{ changeFrom }} ₽</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-bo-muted">Предзаказ</dt>
                <dd class="mt-1 text-bo-fg">{{ scheduledAt || "—" }}</dd>
            </div>
        </dl>
        <div
            v-else
            class="space-y-4"
        >
            <UiFormField
                label="Способ оплаты"
                required
                :error="paymentError"
            >
                <select
                    class="mt-1 h-11 w-full border border-bo-border bg-bo-field px-3 text-sm text-bo-fg"
                    :value="paymentMethod"
                    @change="$emit('update:paymentMethod', $event.target.value)"
                >
                    <option value="cash">Наличные</option>
                    <option value="card_courier">Карта курьеру</option>
                    <option value="card_online">Карта онлайн</option>
                </select>
            </UiFormField>
            <UiFormField
                v-if="paymentMethod === 'cash'"
                label="Сдача с (₽)"
            >
                <UiInput
                    type="number"
                    :model-value="changeFrom"
                    @update:model-value="$emit('update:changeFrom', $event)"
                />
            </UiFormField>
            <UiFormField label="Предзаказ (дата и время)">
                <input
                    type="datetime-local"
                    class="mt-1 h-11 w-full border border-bo-border bg-bo-field px-3 text-sm text-bo-fg"
                    :value="scheduledAt"
                    @input="$emit('update:scheduledAt', $event.target.value)"
                >
            </UiFormField>
        </div>
    </UiCard>
</template>
