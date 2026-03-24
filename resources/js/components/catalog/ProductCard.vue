<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick } from "vue";
import { useCartStore } from "../../stores/cartStore";
import { playTooltipOpen, playTooltipClose } from "../../animations/animationManager";
import {
    getProductNutritionNumbers,
    hasProductNutrition,
} from "../../utils/catalog/productNutrition";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["imageClick"]);

const showNutritionTooltip = ref(false);
const nutritionTriggerRef = ref(null);
const nutritionTooltipRef = ref(null);

const nutrition = computed(() =>
    getProductNutritionNumbers(props.product),
);

const hasNutrition = computed(() => hasProductNutrition(props.product));

function toggleNutritionTooltip() {
    if (showNutritionTooltip.value) {
        playTooltipClose(nutritionTooltipRef.value, () => {
            showNutritionTooltip.value = false;
        });
    } else {
        showNutritionTooltip.value = true;
        nextTick(() => {
            playTooltipOpen(nutritionTooltipRef.value);
        });
    }
}

function closeNutritionTooltip() {
    if (!showNutritionTooltip.value) return;
    playTooltipClose(nutritionTooltipRef.value, () => {
        showNutritionTooltip.value = false;
    });
}

function openNutritionTooltip() {
    if (showNutritionTooltip.value) return;
    showNutritionTooltip.value = true;
    nextTick(() => playTooltipOpen(nutritionTooltipRef.value));
}

let clickOutsideHandler = null;
onMounted(() => {
    clickOutsideHandler = (e) => {
        if (!showNutritionTooltip.value) return;
        if (nutritionTriggerRef.value?.contains(e.target)) return;
        closeNutritionTooltip();
    };
    document.addEventListener("click", clickOutsideHandler);
});
onUnmounted(() => {
    if (clickOutsideHandler) {
        document.removeEventListener("click", clickOutsideHandler);
    }
});

const primaryThumb = computed(() => {
    const p = props.product || {};
    if (Array.isArray(p.images) && p.images.length) {
        return p.images[0];
    }
    return null;
});

const imageSrcset = computed(() => {
    const list = props.product?.imageSrcset;
    if (!Array.isArray(list) || list.length === 0) return null;
    return list
        .map(({ url, width }) => (url && width ? `${url} ${width}w` : null))
        .filter(Boolean)
        .join(", ");
});

const imageSizes =
    "(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw";

const cartStore = useCartStore();

const productId = computed(() => props.product.id);

const qtyInCart = computed(() =>
    productId.value ? cartStore.cartQuantityByProduct(productId.value) : 0,
);

const isFav = computed(() =>
    productId.value ? cartStore.isFavorite(productId.value) : false,
);

const handleAddToCart = () => {
    if (!productId.value) return;
    cartStore.addToCart(props.product, 1);
};

const handleInc = () => {
    if (!productId.value) return;
    cartStore.incrementCart(productId.value);
};

const handleDec = () => {
    if (!productId.value) return;
    cartStore.decrementCart(productId.value);
};

const handleToggleFavorite = () => {
    if (!productId.value) return;
    cartStore.toggleFavorite(props.product);
};
</script>

<template>
    <article
        class="group flex h-full flex-col overflow-hidden rounded-2xl sm:rounded-3xl bg-[rgba(255,255,255,0.02)] shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.03)]"
    >
        <div
            class="relative w-full overflow-hidden aspect-[4/3] sm:aspect-[5/4] lg:h-full lg:aspect-auto"
        >
            <img
                v-if="primaryThumb"
                :src="primaryThumb"
                :srcset="imageSrcset || undefined"
                :sizes="imageSrcset ? imageSizes : undefined"
                alt=""
                class="absolute inset-0 h-full w-full object-cover object-center transition-transform duration-500 ease-out group-hover:scale-105"
                loading="lazy"
                fetchpriority="low"
            />
            <div
                v-if="!primaryThumb"
                class="absolute inset-0 flex items-center justify-center bg-slate-900/70 text-xs text-slate-400"
            >
                Нет фото
            </div>

            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10"
            ></div>

            <div
                class="absolute inset-0 z-[1] cursor-pointer"
                aria-label="Открыть карточку товара"
                @click.stop="emit('imageClick', product)"
            ></div>

            <div
                v-if="product.weight"
                class="absolute left-2.5 top-2.5 z-10 inline-flex items-center rounded-full border border-white/10 bg-[rgba(0,0,0,0.75)] px-2 py-1 text-[10px] font-medium text-slate-100 backdrop-blur sm:left-3 sm:top-3 sm:px-2.5 sm:text-[11px]"
            >
                {{ product.weight }} г
            </div>

            <div
                ref="nutritionTriggerRef"
                class="absolute right-2.5 top-2.5 z-10 flex items-center gap-1.5 sm:right-3 sm:top-3"
                @mouseleave="closeNutritionTooltip"
            >
                <template v-if="hasNutrition">
                    <div class="relative">
                        <button
                            type="button"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/20 bg-black/60 text-slate-200 backdrop-blur transition-colors hover:border-amber-400/60 hover:text-amber-200 sm:h-6 sm:w-6"
                            aria-label="Пищевая ценность на 100 г"
                            @click.stop="toggleNutritionTooltip"
                            @mouseenter="openNutritionTooltip"
                        >
                            <i class="mdi mdi-information-outline text-sm sm:text-xs" />
                        </button>
                        <div
                            v-show="showNutritionTooltip"
                            ref="nutritionTooltipRef"
                            role="tooltip"
                            class="absolute right-0 top-full z-10 mt-1.5 min-w-[180px] rounded-xl border border-white/10 bg-[rgba(0,0,0,0.94)] px-3 py-2.5 shadow-xl backdrop-blur sm:min-w-[200px]"
                        >
                            <div class="space-y-1.5 text-[11px] text-slate-100 sm:text-xs">
                                <template v-if="nutrition">
                                    <p
                                        v-if="nutrition.calories"
                                        class="flex justify-between gap-4"
                                    >
                                        <span class="text-slate-300">Калории</span>
                                        <span class="font-medium">{{ nutrition.calories }} ккал</span>
                                    </p>
                                    <p
                                        v-if="nutrition.proteins"
                                        class="flex justify-between gap-4"
                                    >
                                        <span class="text-slate-300">Белки</span>
                                        <span class="font-medium">{{ nutrition.proteins }} г</span>
                                    </p>
                                    <p
                                        v-if="nutrition.fats"
                                        class="flex justify-between gap-4"
                                    >
                                        <span class="text-slate-300">Жиры</span>
                                        <span class="font-medium">{{ nutrition.fats }} г</span>
                                    </p>
                                    <p
                                        v-if="nutrition.carbs"
                                        class="flex justify-between gap-4"
                                    >
                                        <span class="text-slate-300">Углеводы</span>
                                        <span class="font-medium">{{ nutrition.carbs }} г</span>
                                    </p>
                                </template>
                                <p class="mt-1.5 border-t border-white/10 pt-1.5 text-[10px] text-slate-400 sm:text-[11px]">
                                    на 100 г
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
                <div
                    v-if="product.price"
                    class="inline-flex items-center rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-semibold text-black shadow-[0_0_20px_rgba(251,191,36,0.7)] sm:px-3 sm:py-1.5 sm:text-xs"
                >
                    {{ product.price }} ₽
                </div>
            </div>

            <div
                class="absolute inset-x-2.5 bottom-2.5 z-10 rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.04)] px-3 py-2.5 backdrop-blur shadow-[0_0_20px_rgba(0,0,0,0.9)] sm:inset-x-3 sm:bottom-3 sm:px-3.5"
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

