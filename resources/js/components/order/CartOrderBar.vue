<script setup>
import { computed } from "vue";
import { useOrderEntryPoints } from "../../composables/order/useOrderEntryPoints";
import { useCartStore } from "../../stores/cartStore";
import { useAppDesign } from "../../design/useAppDesign";

const bar = useAppDesign().components.order.cartOrderBar;
const cartStore = useCartStore();
const { cartSummary, isHome, openCart, startCheckout } = useOrderEntryPoints();

const visible = computed(
    () => isHome.value && cartStore.cartTotalItems > 0,
);
</script>

<template>
    <div
        v-if="visible"
        :class="bar.fixedRoot"
        role="region"
        aria-label="Корзина"
    >
        <div :class="bar.inner">
            <div :class="bar.bar">
                <button
                    type="button"
                    :class="bar.summaryBtn"
                    @click="openCart"
                >
                    <div :class="bar.summaryLabel">
                        Корзина ({{ cartSummary.count }})
                    </div>
                    <div :class="bar.summaryMeta">
                        {{ cartSummary.amountRub }}&nbsp;₽
                    </div>
                </button>
                <button
                    type="button"
                    :class="bar.primaryBtn"
                    @click="startCheckout"
                >
                    Оформить
                </button>
            </div>
        </div>
    </div>
</template>
