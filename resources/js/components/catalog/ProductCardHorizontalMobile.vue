<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from "vue";
import { playTooltipClose, playTooltipOpen } from "../../animations/animationManager";
import { useFixedTooltip } from "../../composables/catalog/useFixedTooltip";
import { useProductActions } from "../../composables/catalog/useProductActions";
import { useProductMeta } from "../../composables/catalog/useProductMeta";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["imageClick"]);

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

const imageSizes = "(max-width: 640px) 40vw, 240px";

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

const { nutrition, hasNutrition, hasIngredients, ingredientsText } =
    useProductMeta(computed(() => props.product));

const openTooltip = ref(null); // nutrition | ingredients | null
const actionsClusterRef = ref(null);
const nutritionButtonRef = ref(null);
const ingredientsButtonRef = ref(null);
const { tooltipRef, tooltipStyle, openAt, close: hideFloatingTooltip } = useFixedTooltip();

function toggleNutritionTooltip() {
    if (openTooltip.value === "nutrition") {
        closeTooltip();
        return;
    }
    openTooltip.value = "nutrition";
    nextTick(async () => {
        await openAt(nutritionButtonRef.value);
        playTooltipOpen(tooltipRef.value);
    });
}

function toggleIngredientsTooltip() {
    if (openTooltip.value === "ingredients") {
        closeTooltip();
        return;
    }
    openTooltip.value = "ingredients";
    nextTick(async () => {
        await openAt(ingredientsButtonRef.value);
        playTooltipOpen(tooltipRef.value);
    });
}

function closeTooltip() {
    const current = tooltipRef.value;
    if (!current) {
        openTooltip.value = null;
        hideFloatingTooltip();
        return;
    }
    playTooltipClose(current, () => {
        openTooltip.value = null;
        hideFloatingTooltip();
    });
}

function handlePriceClick() {
    if (qtyInCart.value === 0) {
        addToCart(1);
        return;
    }
    incrementCart();
}

let outsideClickHandler = null;
onMounted(() => {
    outsideClickHandler = (e) => {
        if (!openTooltip.value) return;
        if (actionsClusterRef.value?.contains(e.target)) return;
        if (tooltipRef.value?.contains(e.target)) return;
        closeTooltip();
    };
    document.addEventListener("click", outsideClickHandler);
});

onUnmounted(() => {
    if (outsideClickHandler) {
        document.removeEventListener("click", outsideClickHandler);
    }
    closeTooltip();
});
</script>

<template>
    <article
        class="group flex overflow-hidden rounded-2xl border border-white/10 bg-[rgba(255,255,255,0.02)] shadow-[0_10px_32px_rgba(0,0,0,0.7)]"
    >
        <div
            class="relative w-28 shrink-0 cursor-pointer overflow-hidden sm:w-32"
            @click.stop="emit('imageClick', product)"
        >
            <img
                v-if="primaryThumb"
                :src="primaryThumb"
                :srcset="imageSrcset || undefined"
                :sizes="imageSrcset ? imageSizes : undefined"
                alt=""
                class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
                fetchpriority="low"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-slate-900/70 text-xs text-slate-400"
            >
                Нет фото
            </div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-black/5 via-black/20 to-black/50" />
        </div>

        <div class="flex min-w-0 flex-1 justify-between gap-3 p-3">
            <div class="min-w-0 flex-1">
                <p
                    class="line-clamp-2 text-sm font-semibold leading-snug text-slate-100"
                    :title="product.name"
                >
                    {{ product.name }}
                </p>
                <p v-if="product.weight" class="mt-1 text-[11px] text-slate-400">
                    {{ product.weight }} г
                </p>

                <div
                    ref="actionsClusterRef"
                    class="relative mt-3 flex flex-wrap items-center gap-2"
                >
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-black/45 text-slate-100 transition hover:border-amber-400/60 hover:text-amber-200"
                        :class="isFav ? 'border-amber-400/60 text-amber-200' : ''"
                        aria-label="Избранное"
                        @click.stop="toggleFavorite"
                    >
                        <i :class="['mdi text-lg', isFav ? 'mdi-heart' : 'mdi-heart-outline']" />
                    </button>

                    <button
                        v-if="hasNutrition"
                        ref="nutritionButtonRef"
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-amber-400/40 bg-black/45 text-amber-200 transition hover:border-amber-400/70"
                        aria-label="Показать КБЖУ"
                        @click.stop="toggleNutritionTooltip"
                    >
                        <i class="mdi mdi-fire-circle text-lg" />
                    </button>

                    <button
                        v-if="hasIngredients"
                        ref="ingredientsButtonRef"
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-black/45 text-slate-100 transition hover:border-amber-400/60 hover:text-amber-200"
                        aria-label="Показать состав"
                        @click.stop="toggleIngredientsTooltip"
                    >
                        <i class="mdi mdi-information-outline text-lg" />
                    </button>

                    <template v-if="qtyInCart === 0">
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-400 text-black transition hover:scale-105"
                            aria-label="Добавить в корзину"
                            @click.stop="addToCart(1)"
                        >
                            <i class="mdi mdi-cart-outline text-lg" />
                        </button>
                    </template>
                    <div
                        v-else
                        class="flex h-9 items-center rounded-full border border-amber-400/50 bg-black/60 px-1"
                    >
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold text-slate-100 transition hover:bg-black/50"
                            aria-label="Уменьшить количество"
                            @click.stop="decrementCart"
                        >
                            –
                        </button>
                        <span class="min-w-[1.25rem] px-1 text-center text-xs font-semibold text-amber-200">
                            {{ qtyInCart }}
                        </span>
                        <button
                            type="button"
                            class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold text-slate-100 transition hover:bg-black/50"
                            aria-label="Увеличить количество"
                            @click.stop="incrementCart"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col items-end justify-between gap-3">
                <button
                    v-if="product.price != null"
                    type="button"
                    class="rounded-lg bg-amber-400 px-2.5 py-1.5 text-xs font-semibold text-black transition hover:scale-[1.03]"
                    :aria-label="qtyInCart === 0 ? 'Добавить в корзину' : 'Увеличить количество'"
                    @click.stop="handlePriceClick"
                >
                    {{ product.price }} ₽
                </button>
                <span v-if="product.weight" class="text-[11px] text-slate-400">{{ product.weight }} г</span>
            </div>
        </div>
    </article>

    <Teleport to="body">
        <div
            v-if="openTooltip"
            ref="tooltipRef"
            class="fixed z-[1300] rounded-xl border border-white/10 bg-[rgba(0,0,0,0.95)] px-2.5 py-2 shadow-xl backdrop-blur"
            :class="openTooltip === 'ingredients' ? 'w-[210px] max-h-44 overflow-y-auto' : 'w-[180px]'"
            :style="tooltipStyle"
            role="dialog"
        >
            <div v-if="openTooltip === 'nutrition'" class="space-y-1 text-[11px] text-slate-100">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-300">Калории</span>
                    <span class="font-medium">{{ nutrition.calories }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-300">Белки</span>
                    <span class="font-medium">{{ nutrition.proteins }} г</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-300">Жиры</span>
                    <span class="font-medium">{{ nutrition.fats }} г</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-300">Углеводы</span>
                    <span class="font-medium">{{ nutrition.carbs }} г</span>
                </div>
            </div>
            <div v-else class="space-y-1 text-[11px] text-slate-100">
                <div class="text-[10px] font-medium text-slate-300">Состав</div>
                <div class="text-slate-200/90">
                    {{ ingredientsText }}
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped></style>
