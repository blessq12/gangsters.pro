<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from "vue";
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

const nutrition = computed(() => getProductNutritionNumbers(props.product));
const hasNutrition = computed(() => hasProductNutrition(props.product));

const ingredients = computed(() => {
    const raw = props.product?.raw?.ingredients;
    if (!Array.isArray(raw)) return [];
    return raw.filter((i) => i && (i.name || i.amount));
});

const hasIngredients = computed(() => ingredients.value.length > 0);

const showNutritionTooltip = ref(false);
const nutritionTriggerRef = ref(null);
const nutritionTooltipRef = ref(null);

function toggleNutritionTooltip() {
    if (showNutritionTooltip.value) {
        playTooltipClose(nutritionTooltipRef.value, () => {
            showNutritionTooltip.value = false;
        });
    } else {
        showNutritionTooltip.value = true;
        nextTick(() => playTooltipOpen(nutritionTooltipRef.value));
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

function handleToggleFavorite() {
    if (!productId.value) return;
    cartStore.toggleFavorite(props.product);
}

function handleAddToCart() {
    if (!productId.value) return;
    cartStore.addToCart(props.product, 1);
}

function handleInc() {
    if (!productId.value) return;
    cartStore.incrementCart(productId.value);
}

function handleDec() {
    if (!productId.value) return;
    cartStore.decrementCart(productId.value);
}

function openMobileDetail() {
    closeNutritionTooltip();
    emit("imageClick", props.product);
}
</script>

<template>
    <article
        class="group flex h-full flex-col overflow-hidden rounded-2xl bg-[rgba(255,255,255,0.02)] shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.03)]"
    >
        <div
            class="relative w-full overflow-hidden aspect-[4/3]"
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
                v-else
                class="absolute inset-0 flex items-center justify-center bg-slate-900/70 text-xs text-slate-400"
            >
                Нет фото
            </div>

            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/45 to-black/10"
            />

            <div
                class="absolute inset-0 z-[1] cursor-pointer"
                aria-label="Открыть карточку товара"
                @click.stop="emit('imageClick', product)"
            />

            <!-- Упрощенный UI: акцент на фото + короткие чипы -->
            <div
                v-if="product.weight"
                class="absolute left-2.5 top-2.5 z-10 inline-flex items-center rounded-full border border-white/10 bg-[rgba(0,0,0,0.75)] px-2 py-1 text-[10px] font-medium text-slate-100 backdrop-blur"
            >
                {{ product.weight }} г
            </div>

            <!-- Только иконки/кнопки: без “острова” с текстом -->
            <button
                type="button"
                class="absolute right-2.5 top-2.5 z-10 flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-black/55 text-slate-200 transition-colors hover:border-amber-400/60 hover:text-amber-200"
                :class="isFav ? 'border-amber-400/60 text-amber-200' : ''"
                aria-label="Избранное"
                @click.stop="handleToggleFavorite"
            >
                <i
                    :class="[
                        'mdi',
                        isFav ? 'mdi-heart' : 'mdi-heart-outline',
                    ]"
                />
            </button>

            <!-- Тултип КБЖУ + кнопки “посмотреть” -->
            <div
                v-if="hasNutrition"
                ref="nutritionTriggerRef"
                class="absolute right-2.5 top-12 z-10"
                @mouseleave="closeNutritionTooltip"
            >
                <div class="relative">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-black/55 text-slate-200 transition-colors hover:border-amber-400/60 hover:text-amber-200"
                        aria-label="Пищевая ценность на 100 г"
                        @click.stop="toggleNutritionTooltip"
                        @mouseenter="openNutritionTooltip"
                    >
                        <i class="mdi mdi-information-outline text-lg" />
                    </button>

                    <div
                        v-show="showNutritionTooltip"
                        ref="nutritionTooltipRef"
                        role="tooltip"
                        class="absolute right-0 top-full z-50 mt-2 w-[220px] rounded-xl border border-white/10 bg-[rgba(0,0,0,0.95)] px-3 py-2.5 shadow-xl backdrop-blur"
                    >
                        <div class="space-y-1.5 text-[11px] text-slate-100">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-300">Калории</span>
                                <span class="font-medium">{{ nutrition.calories }} ккал</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-300">Белки</span>
                                <span class="font-medium">{{ nutrition.proteins }} г</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-300">Жиры</span>
                                <span class="font-medium">{{ nutrition.fats }} г</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-300">Углеводы</span>
                                <span class="font-medium">{{ nutrition.carbs }} г</span>
                            </div>
                        </div>

                        <!-- Кнопки под тултипом: открывают модалку -->
                        <div class="mt-2 flex items-center gap-2">
                            <button
                                v-if="hasNutrition"
                                type="button"
                                class="flex h-9 flex-1 items-center justify-center rounded-full border border-amber-400/40 bg-black/70 text-amber-200 transition hover:border-amber-400/70"
                                aria-label="Открыть КБЖУ"
                                @click.stop="openMobileDetail"
                            >
                                <i class="mdi mdi-information-outline" />
                            </button>
                            <button
                                v-if="hasIngredients"
                                type="button"
                                class="flex h-9 flex-1 items-center justify-center rounded-full border border-white/10 bg-black/70 text-slate-200 transition hover:border-amber-400/50"
                                aria-label="Открыть состав"
                                @click.stop="openMobileDetail"
                            >
                                <i class="mdi mdi-information-outline" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Управление корзиной -->
            <div
                class="absolute inset-x-2 bottom-2 z-10 rounded-2xl border border-amber-400/25 bg-[rgba(255,255,255,0.04)] px-2.5 py-2 backdrop-blur shadow-[0_0_20px_rgba(0,0,0,0.9)]"
            >
                <div class="flex items-center justify-between gap-2">
                    <div
                        v-if="product.price"
                        class="inline-flex items-center rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-semibold text-black shadow-[0_0_20px_rgba(251,191,36,0.7)]"
                    >
                        {{ product.price }} ₽
                    </div>

                    <div class="flex items-center gap-2">
                    <button
                        v-if="qtyInCart === 0"
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-black shadow-[0_0_12px_rgba(251,191,36,0.45)] transition-transform hover:scale-[1.02]"
                        aria-label="В корзину"
                        @click.stop="handleAddToCart"
                    >
                        <i class="mdi mdi-cart-outline text-xl" />
                    </button>

                    <template v-else>
                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-amber-400/50 bg-black/60 text-amber-200 transition-colors hover:border-amber-400/70"
                            aria-label="Уменьшить количество"
                            @click.stop="handleDec"
                        >
                            <i class="mdi mdi-minus text-xl" />
                        </button>

                        <span
                            class="min-w-[2.5ch] text-center font-semibold text-sm text-slate-100 tabular-nums"
                            aria-label="Количество в корзине"
                        >
                            {{ qtyInCart }}
                        </span>

                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-amber-400/50 bg-black/60 text-amber-200 transition-colors hover:border-amber-400/70"
                            aria-label="Увеличить количество"
                            @click.stop="handleInc"
                        >
                            <i class="mdi mdi-plus text-xl" />
                        </button>
                    </template>
                    </div>
                </div>
            </div>
        </div>
    </article>
</template>

<style scoped></style>

