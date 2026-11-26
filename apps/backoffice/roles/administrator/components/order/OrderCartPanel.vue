<script setup>
import OrderMenuDropdown from "../../../../shell/components/menu/OrderMenuDropdown.vue";

defineProps({
    /** @type {Array<{ key: string, id: number, kind: string, name: string, quantity: number }>} */
    cart: { type: Array, default: () => [] },
    freeComplementLines: { type: Array, default: () => [] },
    giftLines: { type: Array, default: () => [] },
    /** Категории меню филиала */
    categories: { type: Array, default: () => [] },
    /** Плоский список меню (fallback) */
    menuItems: {
        type: Array,
        default: () => [],
    },
    menuLoading: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    emptyText: { type: String, default: "Добавь позиции в заказ." },
});

defineEmits(["change-qty", "remove", "add"]);
</script>

<template>
    <div class="space-y-3">
        <OrderMenuDropdown
            v-if="!readonly"
            :categories="categories"
            :menu-items="menuItems"
            :loading="menuLoading"
            :disabled="readonly"
            @add="$emit('add', $event)"
        />

        <p
            v-if="cart.length === 0 && freeComplementLines.length === 0 && giftLines.length === 0"
            class="text-sm text-bo-muted"
        >
            {{ emptyText }}
        </p>
        <div v-else class="max-h-[36rem] space-y-2 overflow-y-auto">
            <div
                v-for="line in cart"
                :key="line.key"
                class="flex items-center justify-between gap-2 border border-bo-border bg-bo-field px-3 py-2"
            >
                <div class="min-w-0">
                    <div class="truncate text-sm text-bo-fg">
                        {{ line.name }}
                    </div>
                    <div class="text-xs text-bo-muted">
                        {{ line.kind === "set" ? "Набор" : "Товар" }}
                    </div>
                </div>
                <div v-if="!readonly" class="flex shrink-0 items-center gap-1">
                    <button
                        type="button"
                        class="h-8 w-8 border border-bo-border text-bo-fg"
                        @click="$emit('change-qty', line.key, -1)"
                    >
                        −
                    </button>
                    <span class="w-6 text-center text-sm">{{ line.quantity }}</span>
                    <button
                        type="button"
                        class="h-8 w-8 border border-bo-border text-bo-fg"
                        @click="$emit('change-qty', line.key, 1)"
                    >
                        +
                    </button>
                    <button
                        type="button"
                        class="ml-1 h-8 px-2 text-xs text-bo-muted"
                        @click="$emit('remove', line.key)"
                    >
                        ×
                    </button>
                </div>
                <span v-else class="shrink-0 text-sm text-bo-fg">× {{ line.quantity }}</span>
            </div>

            <template v-if="freeComplementLines.length > 0">
                <p class="pt-1 text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                    Бесплатный комплект
                </p>
                <div
                    v-for="(line, idx) in freeComplementLines"
                    :key="`free-c-${line.product_id}-${idx}`"
                    class="flex items-center justify-between gap-2 border border-dashed border-bo-border bg-white/[0.02] px-3 py-2"
                >
                    <div class="min-w-0">
                        <div class="truncate text-sm text-bo-fg">
                            {{ line.product_name || line.name }}
                        </div>
                        <div class="text-xs text-bo-muted">бесплатно × {{ line.quantity }}</div>
                    </div>
                    <span class="shrink-0 text-sm text-bo-muted">0 ₽</span>
                </div>
            </template>

            <template v-if="giftLines.length > 0">
                <p class="pt-1 text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                    Подарок
                </p>
                <div
                    v-for="(line, idx) in giftLines"
                    :key="`gift-${line.product_id}-${idx}`"
                    class="flex items-center justify-between gap-2 border border-dashed border-bo-border bg-white/[0.02] px-3 py-2"
                >
                    <div class="truncate text-sm text-bo-fg">
                        {{ line.product_name || line.name }} × {{ line.quantity }}
                    </div>
                    <span class="shrink-0 text-sm text-bo-muted">0 ₽</span>
                </div>
            </template>
        </div>
    </div>
</template>
