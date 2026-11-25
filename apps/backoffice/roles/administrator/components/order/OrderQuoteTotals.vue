<script setup>
defineProps({
    cartCount: { type: Number, default: 0 },
    itemsRubles: { type: Number, default: 0 },
    deliveryRubles: { type: Number, default: 0 },
    grandRubles: { type: Number, default: 0 },
    loading: { type: Boolean, default: false },
    showDelivery: { type: Boolean, default: false },
});

function rubles(value) {
    return `${Number(value || 0)} ₽`;
}
</script>

<template>
    <div class="space-y-1 border-t border-bo-border pt-3 text-sm">
        <div class="flex justify-between text-bo-muted">
            <span>{{ cartCount }} шт.</span>
            <span>{{ rubles(itemsRubles) }}</span>
        </div>
        <div
            v-if="showDelivery"
            class="flex justify-between text-bo-muted"
        >
            <span>Доставка</span>
            <span>{{ rubles(deliveryRubles) }}</span>
        </div>
        <div class="flex justify-between font-medium text-bo-fg">
            <span>Итого</span>
            <span>{{ rubles(grandRubles || itemsRubles) }}</span>
        </div>
        <p
            v-if="loading"
            class="text-xs text-bo-muted"
        >
            Пересчёт…
        </p>
    </div>
</template>
