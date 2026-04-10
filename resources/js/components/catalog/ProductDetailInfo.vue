<script setup>
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
} from "vue";
import { playTooltipClose, playTooltipOpen } from "../../animations/animationManager";
import { useProductMeta } from "../../composables/catalog/useProductMeta";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
    qtyInCart: {
        type: Number,
        default: 0,
    },
    isFav: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "add-to-cart",
    "increment",
    "decrement",
    "toggle-favorite",
]);

const { nutrition, hasNutrition, ingredients, ingredientsText } =
    useProductMeta(computed(() => props.product));

const tags = computed(() => {
    const source = Array.isArray(props.product?.tags)
        ? props.product.tags
        : props.product?.raw?.tags;
    if (!Array.isArray(source)) return [];
    return source
        .map((tag) => {
            const code = String(tag?.code || "").trim();
            if (!code) return null;

            return {
                code,
                label: String(tag?.label || code).trim(),
                color: String(tag?.color || "amber").trim().toLowerCase(),
            };
        })
        .filter(Boolean);
});

function tagColorClass(color) {
    if (color === "red") return "border-red-400/50 bg-red-500/20 text-red-100";
    if (color === "green") return "border-green-400/50 bg-green-500/20 text-green-100";
    if (color === "slate") return "border-slate-400/50 bg-slate-500/20 text-slate-100";
    if (color === "sky") return "border-sky-400/50 bg-sky-500/20 text-sky-100";
    if (color === "violet") return "border-violet-400/50 bg-violet-500/20 text-violet-100";
    return "border-amber-400/60 bg-amber-500/20 text-amber-100";
}

const activeTooltip = ref(null); // 'nutrition' | 'ingredients' | null
const tooltipPosition = ref({ left: 0, top: 0 });
const tooltipRef = ref(null);

const nutritionBtnRef = ref(null);
const ingredientsBtnRef = ref(null);

const tooltipWidthClass = computed(() =>
    activeTooltip.value === "ingredients"
        ? "w-[260px]"
        : "w-[240px]",
);

function getAnchorEl(type) {
    if (type === "nutrition") return nutritionBtnRef.value;
    if (type === "ingredients") return ingredientsBtnRef.value;
    return null;
}

function closeTooltip() {
    if (!activeTooltip.value) return;
    const currentEl = tooltipRef.value;
    if (!currentEl) {
        activeTooltip.value = null;
        return;
    }
    playTooltipClose(currentEl, () => {
        activeTooltip.value = null;
    });
}

function computeTooltipPosition() {
    const type = activeTooltip.value;
    if (!type) return;

    const anchorEl = getAnchorEl(type);
    const anchorRect = anchorEl?.getBoundingClientRect();
    const tipRect = tooltipRef.value?.getBoundingClientRect();

    if (!anchorRect || !tipRect) return;

    const margin = 8;

    // Открываем от левого края кнопки (вправо),
    // и зажимаем в пределах экрана.
    let left = anchorRect.left;
    left = Math.max(
        margin,
        Math.min(left, window.innerWidth - tipRect.width - margin),
    );

    // Открываем наверх
    let top = anchorRect.top - tipRect.height - margin;
    top = Math.max(margin, top);

    tooltipPosition.value = { left, top };
}

async function openTooltip(type) {
    if (type === "nutrition" && !hasNutrition.value) return;
    if (type === "ingredients" && !ingredients.value.length) return;

    activeTooltip.value = type;
    await nextTick();
    computeTooltipPosition();
    playTooltipOpen(tooltipRef.value);
}

function toggleNutritionTooltip() {
    if (activeTooltip.value === "nutrition") {
        closeTooltip();
        return;
    }
    openTooltip("nutrition");
}

function toggleIngredientsTooltip() {
    if (activeTooltip.value === "ingredients") {
        closeTooltip();
        return;
    }
    openTooltip("ingredients");
}

let outsideClickHandler = null;
onMounted(() => {
    outsideClickHandler = (e) => {
        if (!activeTooltip.value) return;

        const tipEl = tooltipRef.value;
        const nutritionEl = nutritionBtnRef.value;
        const ingredientsEl = ingredientsBtnRef.value;

        if (
            tipEl?.contains(e.target) ||
            nutritionEl?.contains(e.target) ||
            ingredientsEl?.contains(e.target)
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

function handleAddToCart() {
    emit("add-to-cart");
}

function handlePriceClick() {
    // UX: клик по цене добавляет товар или увеличивает количество.
    if (props.qtyInCart === 0) {
        handleAddToCart();
        return;
    }
    emit("increment");
}

function handleIncrement() {
    emit("increment");
}

function handleDecrement() {
    emit("decrement");
}

function handleToggleFavorite() {
    emit("toggle-favorite");
}
</script>

<template>
    <div
        v-if="product"
        class="product-detail-info__card"
    >
        <div class="w-2/3 min-w-0">
            <h2
                class="rounded-xl bg-black/35 px-2.5 py-2 text-[13px] font-semibold leading-snug text-slate-50 line-clamp-3 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur"
                :title="product.name || product.raw?.name || 'Без названия'"
            >
                {{ product.name || product.raw?.name || "Без названия" }}
            </h2>
            <div
                v-if="tags.length"
                class="mt-2 flex flex-wrap gap-1.5"
            >
                <span
                    v-for="tag in tags"
                    :key="tag.code"
                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium backdrop-blur"
                    :class="tagColorClass(tag.color)"
                >
                    {{ tag.label }}
                </span>
            </div>
        </div>

        <div class="flex w-full shrink-0 items-center gap-3">
            <div class="relative flex w-fit min-w-0 items-center gap-2.5 rounded-2xl border border-white/10 bg-black/35 px-3 py-1.5 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur">
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-black/55 text-slate-200 transition-colors hover:border-amber-400/60 hover:text-amber-200"
                    :class="isFav ? 'border-amber-400/60 text-amber-200' : ''"
                    aria-label="Избранное"
                    @click.stop="handleToggleFavorite"
                >
                    <i :class="['mdi text-xl', isFav ? 'mdi-heart' : 'mdi-heart-outline']" />
                </button>

                <button
                    v-if="hasNutrition"
                    type="button"
                    ref="nutritionBtnRef"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-amber-400/40 bg-black/55 text-amber-200 transition-colors hover:border-amber-400/70 hover:text-amber-200"
                    aria-label="Показать КБЖУ"
                    @click.stop="toggleNutritionTooltip"
                >
                    <i class="mdi mdi-fire-circle text-xl" />
                </button>

                <button
                    v-if="ingredients.length"
                    type="button"
                    ref="ingredientsBtnRef"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/55 text-slate-200 transition-colors hover:border-amber-400/50 hover:text-amber-200"
                    aria-label="Показать состав"
                    @click.stop="toggleIngredientsTooltip"
                >
                    <i class="mdi mdi-information-outline text-xl" />
                </button>

                <div class="flex h-10 shrink-0 items-center">
                    <button
                        v-if="qtyInCart === 0"
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-black"
                        aria-label="Добавить в корзину"
                        @click.stop="handleAddToCart"
                    >
                        <i class="mdi mdi-cart-outline text-xl" />
                    </button>
                    <div
                        v-else
                        class="flex h-10 items-center gap-0.5 rounded-full border border-amber-400/50 bg-black/60 px-0.5"
                    >
                        <button
                            type="button"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-black/50 text-base font-semibold leading-none text-slate-100"
                            aria-label="Уменьшить количество"
                            @click.stop="handleDecrement"
                        >
                            –
                        </button>
                        <span class="min-w-[1.5rem] px-0.5 text-center text-[12px] font-semibold tabular-nums text-amber-200">
                            {{ qtyInCart }}
                        </span>
                        <button
                            type="button"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-black/50 text-base font-semibold leading-none text-slate-100"
                            aria-label="Увеличить количество"
                            @click.stop="handleIncrement"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>

            <button
                v-if="product.price != null"
                class="ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap rounded-lg bg-amber-400 px-3 py-1.5 text-[12px] font-semibold text-black transition-transform duration-200 hover:scale-[1.03] cursor-pointer"
                @click.stop="handlePriceClick"
                type="button"
                :aria-label="qtyInCart === 0 ? 'Добавить в корзину' : 'Увеличить количество'"
            >
                {{ formatMoneyRublesRu(product.price) }}&nbsp;₽
            </button>
        </div>
    </div>

    <Teleport to="body">
        <div
            v-if="activeTooltip"
            ref="tooltipRef"
            class="fixed z-[10000] rounded-xl border border-white/10 bg-[rgba(0,0,0,0.95)] px-3 py-2.5 shadow-xl backdrop-blur max-h-44 overflow-y-auto"
            :class="tooltipWidthClass"
            :style="{
                left: `${tooltipPosition.left}px`,
                top: `${tooltipPosition.top}px`,
            }"
            role="dialog"
        >
            <template v-if="activeTooltip === 'nutrition'">
                <div class="space-y-2 text-[11px] text-slate-100">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-300">Калории</span>
                        <span class="font-medium">
                            {{ nutrition.calories }} ккал
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-300">Белки</span>
                        <span class="font-medium">
                            {{ nutrition.proteins }} г
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-300">Жиры</span>
                        <span class="font-medium">
                            {{ nutrition.fats }} г
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-300">Углеводы</span>
                        <span class="font-medium">
                            {{ nutrition.carbs }} г
                        </span>
                    </div>
                </div>
            </template>

            <template v-else-if="activeTooltip === 'ingredients'">
                <div class="space-y-2 text-[11px] text-slate-100">
                    <div class="text-[10px] font-medium text-slate-300">
                        Состав
                    </div>
                    <div class="break-words text-slate-200/90">
                        {{ ingredientsText }}
                    </div>
                </div>
            </template>
        </div>
    </Teleport>
</template>

<style scoped>
.product-detail-info__card {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
</style>
