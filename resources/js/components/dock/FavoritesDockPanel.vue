<script setup>
import { computed } from "vue";
import { useCartStore } from "../../stores/cartStore";

const cartStore = useCartStore();

const favoriteItems = computed(() => cartStore.favorites);

const handleAddToCart = (item) => {
    if (!item?.productSnapshot?.id) return;
    cartStore.addToCart(item.productSnapshot, 1);
};

const formatPrice = (value) =>
    new Intl.NumberFormat("ru-RU").format(Number(value) || 0);
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-3">
            <p class="text-sm sm:text-base font-semibold text-slate-50">
                Избранное
            </p>

            <div
                v-if="!favoriteItems.length"
                class="rounded-2xl bg-[rgba(255,255,255,0.03)] px-4 py-5 text-sm text-slate-300"
            >
                Избранное пока пустое. Ткни сердечко на карточке, и позиция появится тут.
            </div>

            <ul
                v-else
                class="space-y-2 text-xs sm:text-sm text-slate-200"
            >
                <li
                    v-for="item in favoriteItems"
                    :key="item.productId"
                    class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium text-slate-100">
                            {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-slate-400">
                            {{ formatPrice(item.productSnapshot?.price) }} ₽
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="shrink-0 text-[11px] text-amber-300 transition-colors hover:text-amber-200"
                            @click="handleAddToCart(item)"
                        >
                            В корзину
                        </button>
                        <button
                            type="button"
                            class="shrink-0 text-[11px] text-slate-400 transition-colors hover:text-red-400"
                            @click="cartStore.removeFavorite(item.productId)"
                        >
                            Убрать
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped></style>

