<script setup>
import UiCard from "../../../../shell/components/ui/UiCard.vue";
import ClientSearchField from "./ClientSearchField.vue";

defineProps({
    name: { type: String, default: "" },
    phone: { type: String, default: "" },
    clientId: { type: [Number, String, null], default: null },
    nameError: { type: String, default: "" },
    phoneError: { type: String, default: "" },
    readonly: { type: Boolean, default: false },
});

defineEmits([
    "update:name",
    "update:phone",
    "select-client",
    "clear-client",
    "apply-address",
    "apply-order",
]);
</script>

<template>
    <UiCard title="Клиент">
        <dl v-if="readonly" class="grid gap-3">
            <div>
                <dt class="text-xs uppercase tracking-wide text-bo-muted">Имя</dt>
                <dd class="mt-1 text-sm text-bo-fg">
                    {{ name || "—" }}
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide text-bo-muted">Телефон</dt>
                <dd class="mt-1 text-sm text-bo-fg">
                    {{ phone || "—" }}
                </dd>
            </div>
        </dl>
        <ClientSearchField
            v-else
            :name="name"
            :phone="phone"
            :client-id="clientId"
            :name-error="nameError"
            :phone-error="phoneError"
            @update:name="$emit('update:name', $event)"
            @update:phone="$emit('update:phone', $event)"
            @select-client="$emit('select-client', $event)"
            @clear-client="$emit('clear-client')"
            @apply-address="$emit('apply-address', $event)"
            @apply-order="$emit('apply-order', $event)"
        />
    </UiCard>
</template>
