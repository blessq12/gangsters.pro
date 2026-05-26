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
import { useAppDesign } from "../../design/useAppDesign";

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

const di = useAppDesign().components.catalog.modal.detailInfo;
const tagTone = useAppDesign().components.catalog.cards.shared.tagTone;

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

function tagToneClass(color) {
    const c = String(color || "").trim().toLowerCase();
    return tagTone[c] ?? tagTone.default;
}

const activeTooltip = ref(null); // 'nutrition' | 'ingredients' | null
const tooltipPosition = ref({ left: 0, top: 0 });
const tooltipRef = ref(null);

const nutritionBtnRef = ref(null);
const ingredientsBtnRef = ref(null);

const tooltipWidthClass = computed(() =>
    activeTooltip.value === "ingredients"
        ? di.tooltipWide
        : di.tooltipNarrow,
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

    let left = anchorRect.left;
    left = Math.max(
        margin,
        Math.min(left, window.innerWidth - tipRect.width - margin),
    );

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
        :class="di.card"
    >
        <div :class="di.titleCol">
            <h2
                :class="di.title"
                :title="product.name || product.raw?.name || 'Без названия'"
            >
                {{ product.name || product.raw?.name || "Без названия" }}
            </h2>
            <div
                v-if="tags.length"
                :class="di.tagsRow"
            >
                <span
                    v-for="tag in tags"
                    :key="tag.code"
                    :class="[di.tagPill, tagToneClass(tag.color)]"
                >
                    {{ tag.label }}
                </span>
            </div>
        </div>

        <div :class="di.controlsRow">
            <div :class="di.actionIsland">
                <button
                    type="button"
                    :class="[di.favBtn, isFav ? di.favBtnActive : '']"
                    aria-label="Избранное"
                    @click.stop="handleToggleFavorite"
                >
                    <i :class="[di.favIcon, isFav ? 'mdi-heart' : 'mdi-heart-outline']" />
                </button>

                <button
                    v-if="hasNutrition"
                    type="button"
                    ref="nutritionBtnRef"
                    :class="di.nutritionBtn"
                    aria-label="Показать КБЖУ"
                    @click.stop="toggleNutritionTooltip"
                >
                    <i :class="di.nutritionIcon" />
                </button>

                <button
                    v-if="ingredients.length"
                    type="button"
                    ref="ingredientsBtnRef"
                    :class="di.ingredientsBtn"
                    aria-label="Показать состав"
                    @click.stop="toggleIngredientsTooltip"
                >
                    <i :class="di.ingredientsIcon" />
                </button>

                <div :class="di.cartIconOuter">
                    <button
                        v-if="qtyInCart === 0"
                        type="button"
                        :class="di.cartAddIconBtn"
                        aria-label="Добавить в корзину"
                        @click.stop="handleAddToCart"
                    >
                        <i :class="di.cartIcon" />
                    </button>
                    <div
                        v-else
                        :class="di.qtyCluster"
                    >
                        <button
                            type="button"
                            :class="di.qtyMiniBtn"
                            aria-label="Уменьшить количество"
                            @click.stop="handleDecrement"
                        >
                            –
                        </button>
                        <span :class="di.qtyNum">
                            {{ qtyInCart }}
                        </span>
                        <button
                            type="button"
                            :class="di.qtyMiniBtn"
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
                :class="di.priceBtn"
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
            :class="[di.teleportTooltipBase, tooltipWidthClass]"
            :style="{
                left: `${tooltipPosition.left}px`,
                top: `${tooltipPosition.top}px`,
            }"
            role="dialog"
        >
            <template v-if="activeTooltip === 'nutrition'">
                <div :class="di.nutritionBlock">
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Калории</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.calories }} ккал
                        </span>
                    </div>
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Белки</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.proteins }} г
                        </span>
                    </div>
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Жиры</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.fats }} г
                        </span>
                    </div>
                    <div :class="di.nutritionRow">
                        <span :class="di.nutritionLabel">Углеводы</span>
                        <span :class="di.nutritionVal">
                            {{ nutrition.carbs }} г
                        </span>
                    </div>
                </div>
            </template>

            <template v-else-if="activeTooltip === 'ingredients'">
                <div :class="di.ingredientsBlock">
                    <div :class="di.ingredientsHeading">
                        Состав
                    </div>
                    <div :class="di.ingredientsBody">
                        {{ ingredientsText }}
                    </div>
                </div>
            </template>
        </div>
    </Teleport>
</template>
