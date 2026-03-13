<script setup>
import { computed } from "vue";
import { useUserStore } from "../../stores/userStore";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

console.debug("[Catalog] product payload", props.product);

const primaryThumb = computed(() => {
    const p = props.product || {};

    // images: ["/storage/..."]
    if (Array.isArray(p.images) && p.images.length) {
        return p.images[0];
    }

    return null;
});

const backgroundStyle = computed(() => {
    if (!primaryThumb.value) {
        return {};
    }

    return {
        backgroundImage: `url(${primaryThumb.value})`,
    };
});

const userStore = useUserStore();

const productId = computed(() => props.product.id);

const qtyInCart = computed(() =>
    productId.value ? userStore.cartQuantityByProduct(productId.value) : 0,
);

const isFav = computed(() =>
    productId.value ? userStore.isFavorite(productId.value) : false,
);

const handleAddToCart = () => {
    if (!productId.value) return;
    userStore.addToCart(props.product, 1);
};

const handleInc = () => {
    if (!productId.value) return;
    userStore.incrementCart(productId.value);
};

const handleDec = () => {
    if (!productId.value) return;
    userStore.decrementCart(productId.value);
};

const handleToggleFavorite = () => {
    if (!productId.value) return;
    userStore.toggleFavorite(props.product);
};
</script>

<template>
    <article
        class="group flex h-full flex-col overflow-hidden rounded-2xl sm:rounded-3xl bg-[rgba(255,255,255,0.02)] shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.03)]"
    >
        <div
            class="relative w-full overflow-hidden aspect-[4/3] sm:aspect-[5/4] lg:h-full lg:aspect-auto"
        >
            <div
                class="absolute inset-0 bg-cover bg-center transition-transform duration-500 ease-out group-hover:scale-105"
                :style="backgroundStyle"
                :class="!primaryThumb ? 'bg-slate-900/70' : ''"
            ></div>

            <div
                v-if="!primaryThumb"
                class="absolute inset-0 flex items-center justify-center text-xs text-slate-400"
            >
                Нет фото
            </div>

            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10"
            ></div>

            <div
                v-if="product.weight"
                class="absolute left-2.5 top-2.5 inline-flex items-center rounded-full border border-white/10 bg-[rgba(0,0,0,0.75)] px-2 py-1 text-[10px] font-medium text-slate-100 backdrop-blur sm:left-3 sm:top-3 sm:px-2.5 sm:text-[11px]"
            >
                {{ product.weight }} г
            </div>

            <div
                v-if="product.price"
                class="absolute right-2.5 top-2.5 inline-flex items-center rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-semibold text-black shadow-[0_0_20px_rgba(251,191,36,0.7)] sm:right-3 sm:top-3 sm:px-3 sm:py-1.5 sm:text-xs"
            >
                {{ product.price }} ₽
            </div>

            <div
                class="absolute inset-x-2.5 bottom-2.5 rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.04)] px-3 py-2.5 backdrop-blur shadow-[0_0_20px_rgba(0,0,0,0.9)] sm:inset-x-3 sm:bottom-3 sm:px-3.5"
            >
                <div class="flex items-start gap-2">
                    <div class="min-w-0 flex-1 space-y-1">
                        <h3
                            class="text-sm font-semibold leading-snug text-slate-50 line-clamp-2 sm:text-base sm:line-clamp-3"
                        >
                            {{ product.name }}
                        </h3>
                        <p
                            v-if="product.consist"
                            class="text-[11px] text-slate-300/85 leading-snug line-clamp-2 sm:text-xs sm:line-clamp-3"
                        >
                            {{ product.consist }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 flex h-9 w-9 items-center justify-center rounded-full border border-white/30 bg-black/60 text-[15px] text-slate-200 transition-colors hover:border-amber-400 hover:text-amber-200 sm:h-7 sm:w-7 sm:text-[13px]"
                        :class="isFav ? 'border-amber-400 text-amber-300' : ''"
                        @click.stop="handleToggleFavorite"
                    >
                        <i
                            :class="[
                                'mdi',
                                isFav ? 'mdi-heart' : 'mdi-heart-outline',
                            ]"
                        />
                    </button>
                </div>

                <div class="mt-2 flex items-center justify-between gap-2">
                    <button
                        v-if="qtyInCart === 0"
                        type="button"
                        class="inline-flex min-h-10 flex-1 items-center justify-center rounded-full bg-amber-400 px-3 py-2 text-xs font-semibold text-black shadow-[0_0_12px_rgba(251,191,36,0.45)] transition-transform hover:scale-[1.02] sm:min-h-0 sm:py-1.5 sm:text-sm"
                        @click.stop="handleAddToCart"
                    >
                        В корзину
                    </button>
                    <div
                        v-else
                        class="inline-flex min-h-10 flex-1 items-center justify-between rounded-full border border-amber-400/60 bg-black/70 px-2 py-1 text-xs text-slate-50"
                    >
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-[16px] sm:h-6 sm:w-6 sm:text-[14px]"
                            @click.stop="handleDec"
                        >
                            –
                        </button>
                        <span class="px-1 text-xs sm:text-sm font-semibold">
                            {{ qtyInCart }} шт
                        </span>
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-[16px] sm:h-6 sm:w-6 sm:text-[14px]"
                            @click.stop="handleInc"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </article>
</template>

<style scoped></style>

