<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from "vue";
import { playTooltipClose, playTooltipOpen } from "../../animations/animationManager";
import { useFixedTooltip } from "../../composables/catalog/useFixedTooltip";
import { useCatalogItemDisplay } from "../../composables/catalog/useCatalogItemDisplay";
import { useProductActions } from "../../composables/catalog/useProductActions";
import { useProductMeta } from "../../composables/catalog/useProductMeta";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["imageClick"]);

const h = useAppDesign().components.catalog.cards.horizontalMobile;
const cs = useAppDesign().components.catalog.cards.shared;

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

const productImageRef = ref(null);

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

function cartFlyOptions() {
    return {
        flySourceEl: productImageRef.value,
        flyImageUrl: primaryThumb.value || undefined,
    };
}

const { nutrition, hasNutrition, hasIngredients, ingredientsText } =
    useProductMeta(computed(() => props.product));
const { isSet, isProduct, setCountLabel, setLines, hasSetComposition } =
    useCatalogItemDisplay(computed(() => props.product));

const openTooltip = ref(null); // nutrition | ingredients | composition | null
const actionsClusterRef = ref(null);
const nutritionButtonRef = ref(null);
const ingredientsButtonRef = ref(null);
const compositionButtonRef = ref(null);
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

function toggleCompositionTooltip() {
    if (openTooltip.value === "composition") {
        closeTooltip();
        return;
    }
    openTooltip.value = "composition";
    nextTick(async () => {
        await openAt(compositionButtonRef.value);
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

function handleAddToCart() {
    addToCart(1, cartFlyOptions());
}

function handleIncrement() {
    incrementCart(cartFlyOptions());
}

function handlePriceClick() {
    if (qtyInCart.value === 0) {
        handleAddToCart();
        return;
    }
    handleIncrement();
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
    <article :class="h.article">
        <div
            :class="h.thumbCol"
            @click.stop="emit('imageClick', product)"
        >
            <img
                v-if="primaryThumb"
                ref="productImageRef"
                :src="primaryThumb"
                :srcset="imageSrcset || undefined"
                :sizes="imageSrcset ? imageSizes : undefined"
                alt=""
                :class="h.thumbImg"
                loading="lazy"
                fetchpriority="low"
            />
            <div
                v-else
                :class="h.thumbPlaceholder"
            >
                {{ cs.noPhotoText }}
            </div>
            <div :class="h.thumbGradient" />
        </div>

        <div :class="h.body">
            <div :class="h.titleRow">
                <p
                    :class="h.title"
                    :title="product.name"
                >
                    {{ product.name }}
                </p>
                <span
                    v-if="isSet && setCountLabel"
                    :class="h.weightInline"
                >
                    {{ setCountLabel }}
                </span>
                <span
                    v-else-if="product.weight"
                    :class="h.weightInline"
                >
                    {{ product.weight }}&nbsp;г
                </span>
            </div>

            <div :class="h.actionsBar">
                <div
                    ref="actionsClusterRef"
                    :class="h.actionCluster"
                >
                    <button
                        type="button"
                        :class="[h.favBtn, isFav ? h.favBtnActive : '']"
                        aria-label="Избранное"
                        @click.stop="toggleFavorite"
                    >
                        <i :class="[h.favIcon, isFav ? 'mdi-heart' : 'mdi-heart-outline']" />
                    </button>

                    <button
                        v-if="hasNutrition && isProduct"
                        ref="nutritionButtonRef"
                        type="button"
                        :class="h.nutritionBtn"
                        aria-label="Показать КБЖУ"
                        @click.stop="toggleNutritionTooltip"
                    >
                        <i :class="h.nutritionIcon" />
                    </button>

                    <button
                        v-if="hasIngredients && isProduct"
                        ref="ingredientsButtonRef"
                        type="button"
                        :class="h.ingredientsBtn"
                        aria-label="Показать состав"
                        @click.stop="toggleIngredientsTooltip"
                    >
                        <i :class="h.ingredientsIcon" />
                    </button>

                    <button
                        v-if="hasSetComposition"
                        ref="compositionButtonRef"
                        type="button"
                        :class="h.ingredientsBtn"
                        aria-label="Показать состав набора"
                        @click.stop="toggleCompositionTooltip"
                    >
                        <i class="mdi mdi-format-list-bulleted text-xl" />
                    </button>

                    <div :class="h.cartIconOuter">
                        <button
                            v-if="qtyInCart === 0"
                            type="button"
                            :class="h.cartAddIconBtn"
                            aria-label="Добавить в корзину"
                            @click.stop="handleAddToCart"
                        >
                            <i :class="h.cartAddIcon" />
                        </button>
                        <div
                            v-else
                            :class="h.qtyCluster"
                        >
                            <button
                                type="button"
                                :class="h.qtyMiniBtn"
                                aria-label="Уменьшить количество"
                                @click.stop="decrementCart"
                            >
                                –
                            </button>
                            <span :class="h.qtyNum">
                                {{ qtyInCart }}
                            </span>
                            <button
                                type="button"
                                :class="h.qtyMiniBtn"
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
                    type="button"
                    :class="h.priceSide"
                    :aria-label="qtyInCart === 0 ? 'Добавить в корзину' : 'Увеличить количество'"
                    @click.stop="handlePriceClick"
                >
                    {{ formatMoneyRublesRu(product.price) }}&nbsp;₽
                </button>
            </div>
        </div>
    </article>

    <Teleport to="body">
        <div
            v-if="openTooltip"
            ref="tooltipRef"
            :class="[
                h.teleportTooltipBase,
                openTooltip === 'nutrition'
                    ? h.teleportTooltipNutritionWidth
                    : h.teleportTooltipIngredientsWidth,
            ]"
            :style="tooltipStyle"
            role="dialog"
        >
            <div v-if="openTooltip === 'nutrition'" :class="h.teleportNutritionInner">
                <div :class="h.teleportNutritionRow">
                    <span :class="h.teleportNutritionLabel">Калории</span>
                    <span :class="h.teleportNutritionVal">{{ nutrition.calories }}</span>
                </div>
                <div :class="h.teleportNutritionRow">
                    <span :class="h.teleportNutritionLabel">Белки</span>
                    <span :class="h.teleportNutritionVal">{{ nutrition.proteins }} г</span>
                </div>
                <div :class="h.teleportNutritionRow">
                    <span :class="h.teleportNutritionLabel">Жиры</span>
                    <span :class="h.teleportNutritionVal">{{ nutrition.fats }} г</span>
                </div>
                <div :class="h.teleportNutritionRow">
                    <span :class="h.teleportNutritionLabel">Углеводы</span>
                    <span :class="h.teleportNutritionVal">{{ nutrition.carbs }} г</span>
                </div>
            </div>
            <div v-else-if="openTooltip === 'ingredients'" :class="h.teleportIngredientsInner">
                <div :class="h.teleportIngredientsHeading">Состав</div>
                <div :class="h.teleportIngredientsBody">
                    {{ ingredientsText }}
                </div>
            </div>
            <div v-else-if="openTooltip === 'composition'" :class="h.teleportIngredientsInner">
                <div :class="h.teleportIngredientsHeading">Состав набора</div>
                <div :class="h.teleportIngredientsBody">
                    <div
                        v-for="line in setLines"
                        :key="line.productId"
                        class="flex justify-between gap-3 py-0.5"
                    >
                        <span>{{ line.productName }}</span>
                        <span class="shrink-0 font-medium tabular-nums text-app-accent">
                            ×{{ line.quantity }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped></style>
