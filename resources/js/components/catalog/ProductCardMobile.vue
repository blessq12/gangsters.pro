<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useCartStore } from "../../stores/cartStore";
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

const ingredientsText = computed(() => {
    // Просто перечисляем состав через запятую для компактного тултипа
    return ingredients.value
        .map((i) => i?.name)
        .filter(Boolean)
        .join(", ");
});

const openTooltip = ref(null); // 'nutrition' | 'ingredients' | null
const nutritionWrapRef = ref(null);
const ingredientsWrapRef = ref(null);

function toggleNutritionTooltip() {
    openTooltip.value =
        openTooltip.value === "nutrition" ? null : "nutrition";
}

function toggleIngredientsTooltip() {
    openTooltip.value =
        openTooltip.value === "ingredients" ? null : "ingredients";
}

function closeTooltip() {
    openTooltip.value = null;
}

let outsideClickHandler = null;
onMounted(() => {
    outsideClickHandler = (e) => {
        if (!openTooltip.value) return;

        if (
            nutritionWrapRef.value?.contains(e.target) ||
            ingredientsWrapRef.value?.contains(e.target)
        ) {
            return;
        }

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
                @click.stop="emit('imageClick', { product, focusSection: null })"
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

            <!-- Управление корзиной -->
            <div
                class="absolute inset-x-2 bottom-2 z-10 rounded-2xl border border-amber-400/25 bg-[rgba(255,255,255,0.04)] px-2.5 py-2 backdrop-blur shadow-[0_0_20px_rgba(0,0,0,0.9)]"
            >
                <div class="space-y-1">
                    <div class="flex items-center justify-between gap-2">
                        <p
                            class="min-w-0 flex-1 text-[11px] font-semibold text-slate-50 line-clamp-1"
                            :title="product.name"
                        >
                            {{ product.name }}
                        </p>

                        <div class="flex items-center gap-1">
                            <div
                                v-if="hasNutrition"
                                ref="nutritionWrapRef"
                                class="relative flex-shrink-0"
                            >
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-full border border-amber-400/40 bg-black/55 text-amber-200 transition-colors hover:border-amber-400/70 hover:text-amber-200"
                                    aria-label="Показать КБЖУ"
                                    @click.stop="toggleNutritionTooltip"
                                >
                                    <i class="mdi mdi-fire-circle text-base" />
                                </button>

                                <div
                                    v-if="openTooltip === 'nutrition'"
                                    class="absolute right-0 bottom-full z-50 mb-1 w-[180px] rounded-lg border border-white/10 bg-[rgba(0,0,0,0.95)] px-2 py-2 shadow-xl backdrop-blur"
                                    role="dialog"
                                >
                                    <div class="space-y-1 text-[10px] text-slate-100">
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
                                </div>
                            </div>

                            <div
                                v-if="hasIngredients"
                                ref="ingredientsWrapRef"
                                class="relative flex-shrink-0"
                            >
                                <button
                                    type="button"
                                    class="flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-black/55 text-slate-200 transition-colors hover:border-amber-400/50 hover:text-amber-200"
                                    aria-label="Показать состав"
                                    @click.stop="toggleIngredientsTooltip"
                                >
                                    <i class="mdi mdi-information-outline text-base" />
                                </button>

                                <div
                                    v-if="openTooltip === 'ingredients'"
                                    class="absolute right-0 bottom-full z-50 mb-1 w-[200px] max-h-36 overflow-y-auto rounded-lg border border-white/10 bg-[rgba(0,0,0,0.95)] px-2 py-2 shadow-xl backdrop-blur"
                                    role="dialog"
                                >
                                    <div class="space-y-1 text-[10px] text-slate-100">
                                        <div class="text-[10px] font-medium text-slate-300">
                                            Состав
                                        </div>
                                        <div class="text-slate-200/90">
                                            {{ ingredientsText }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="product.consist"
                        class="text-[10px] leading-snug text-slate-400 line-clamp-2"
                        :title="product.consist"
                    >
                        {{ product.consist }}
                    </p>

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
        </div>
    </article>
</template>

<style scoped></style>

