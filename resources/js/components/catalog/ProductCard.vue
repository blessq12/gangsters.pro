<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick } from "vue";
import { playTooltipOpen, playTooltipClose } from "../../animations/animationManager";
import { useProductActions } from "../../composables/catalog/useProductActions";
import { useCatalogItemDisplay } from "../../composables/catalog/useCatalogItemDisplay";
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

const d = useAppDesign().components.catalog.cards.desktop;
const cs = useAppDesign().components.catalog.cards.shared;

const showNutritionTooltip = ref(false);
const nutritionTriggerRef = ref(null);
const nutritionTooltipRef = ref(null);

const { nutrition, hasNutrition } = useProductMeta(computed(() => props.product));
const { isSet, isProduct, setCountLabel } = useCatalogItemDisplay(
    computed(() => props.product),
);
const badgeTags = computed(() => {
    const tags =
        (Array.isArray(props.product?.tags) && props.product.tags) ||
        (Array.isArray(props.product?.raw?.tags) && props.product.raw.tags) ||
        (Array.isArray(props.product?.raw?.product_tags) && props.product.raw.product_tags) ||
        [];
    if (!Array.isArray(tags)) return [];

    return tags
        .map((tag) => {
            const code = String(tag?.code || "").trim();
            if (!code) return null;
            return {
                code,
                label: String(tag?.label || code).trim(),
                color: String(tag?.color || "amber").trim().toLowerCase(),
            };
        })
        .filter(Boolean)
        .slice(0, 3);
});

function tagToneClass(color) {
    const c = String(color || "").trim().toLowerCase();
    return cs.tagTone[c] ?? cs.tagTone.default;
}

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

const productImageRef = ref(null);

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

function cartFlyOptions() {
    return {
        flySourceEl: productImageRef.value,
        flyImageUrl: primaryThumb.value || undefined,
    };
}

const handleAddToCart = () => {
    addToCart(1, cartFlyOptions());
};

const handlePriceClick = () => {
    // UX: клик по цене добавляет товар в корзину или увеличивает количество.
    if (qtyInCart.value === 0) {
        handleAddToCart();
        return;
    }
    handleInc();
};

const handleInc = () => {
    incrementCart(cartFlyOptions());
};

const handleDec = () => {
    decrementCart();
};

const handleToggleFavorite = () => {
    toggleFavorite();
};
</script>

<template>
    <article :class="d.article">
        <div :class="d.mediaWrap">
            <img
                v-if="primaryThumb"
                ref="productImageRef"
                :src="primaryThumb"
                :srcset="imageSrcset || undefined"
                :sizes="imageSrcset ? imageSizes : undefined"
                alt=""
                :class="d.img"
                loading="lazy"
                fetchpriority="low"
            />
            <div
                v-if="!primaryThumb"
                :class="d.placeholder"
            >
                {{ cs.noPhotoText }}
            </div>

            <div :class="d.gradient" />

            <div :class="d.badgesCol">
                <span
                    v-if="isSet"
                    :class="cs.setBadge"
                >
                    Набор
                </span>
                <div
                    v-if="isSet && setCountLabel"
                    :class="cs.setCountPill"
                >
                    {{ setCountLabel }}
                </div>
                <div
                    v-else-if="product.weight"
                    :class="d.weightPill"
                >
                    {{ product.weight }} г
                </div>
                <div
                    v-if="badgeTags.length"
                    :class="d.tagsRow"
                >
                    <span
                        v-for="tag in badgeTags"
                        :key="tag.code"
                        :class="[d.tagPill, tagToneClass(tag.color)]"
                    >
                        {{ tag.label }}
                    </span>
                </div>
            </div>

            <div
                :class="d.imageHit"
                aria-label="Открыть карточку товара"
                @click.stop="emit('imageClick', product)"
            />

            <div
                ref="nutritionTriggerRef"
                :class="d.topRightCluster"
                @mouseleave="closeNutritionTooltip"
            >
                <template v-if="hasNutrition && isProduct">
                    <div class="relative">
                        <button
                            type="button"
                            :class="d.nutritionBtn"
                            aria-label="Пищевая ценность на 100 г"
                            @click.stop="toggleNutritionTooltip"
                            @mouseenter="openNutritionTooltip"
                        >
                            <i :class="d.nutritionBtnIcon" />
                        </button>
                        <div
                            v-show="showNutritionTooltip"
                            ref="nutritionTooltipRef"
                            role="tooltip"
                            :class="d.nutritionTooltip"
                        >
                            <div :class="d.nutritionTooltipInner">
                                <template v-if="nutrition">
                                    <p
                                        v-if="nutrition.calories"
                                        :class="d.nutritionRow"
                                    >
                                        <span :class="d.nutritionLabel">Калории</span>
                                        <span :class="d.nutritionVal">{{ nutrition.calories }} ккал</span>
                                    </p>
                                    <p
                                        v-if="nutrition.proteins"
                                        :class="d.nutritionRow"
                                    >
                                        <span :class="d.nutritionLabel">Белки</span>
                                        <span :class="d.nutritionVal">{{ nutrition.proteins }} г</span>
                                    </p>
                                    <p
                                        v-if="nutrition.fats"
                                        :class="d.nutritionRow"
                                    >
                                        <span :class="d.nutritionLabel">Жиры</span>
                                        <span :class="d.nutritionVal">{{ nutrition.fats }} г</span>
                                    </p>
                                    <p
                                        v-if="nutrition.carbs"
                                        :class="d.nutritionRow"
                                    >
                                        <span :class="d.nutritionLabel">Углеводы</span>
                                        <span :class="d.nutritionVal">{{ nutrition.carbs }} г</span>
                                    </p>
                                </template>
                                <p :class="d.nutritionFooter">
                                    на 100 г
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
                <button
                    v-if="product.price"
                    :class="d.priceBadge"
                    type="button"
                    @click.stop="handlePriceClick"
                    :aria-label="qtyInCart === 0 ? 'Добавить в корзину' : 'Увеличить количество'"
                >
                    {{ formatMoneyRublesRu(product.price) }}&nbsp;₽
                </button>
            </div>

            <div
                :class="d.mediaFooterGradient"
                aria-hidden="true"
            />

            <div :class="d.mediaFooterStack">
                <div :class="d.titleRow">
                    <div :class="d.titleText">
                        <h3 :class="d.titleUnderPhoto">
                            {{ product.name }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        :class="[d.favBtn, isFav ? d.favBtnActive : '']"
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

                <div :class="d.cartRow">
                    <button
                        v-if="qtyInCart === 0"
                        type="button"
                        :class="d.addBtn"
                        @click.stop="handleAddToCart"
                    >
                        В корзину
                    </button>
                    <div
                        v-else
                        :class="d.qtyBar"
                    >
                        <button
                            type="button"
                            :class="d.qtyBtn"
                            @click.stop="handleDec"
                        >
                            –
                        </button>
                        <span :class="d.qtyLabel">
                            {{ qtyInCart }} шт
                        </span>
                        <button
                            type="button"
                            :class="d.qtyBtn"
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
