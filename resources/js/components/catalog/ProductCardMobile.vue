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

const m = useAppDesign().components.catalog.cards.mobileGrid;
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

const { nutrition, hasNutrition, hasIngredients, ingredientsText } =
    useProductMeta(computed(() => props.product));
const { isSet, isProduct, setCountLabel, setLines, hasSetComposition } =
    useCatalogItemDisplay(computed(() => props.product));
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

const openTooltip = ref(null); // 'nutrition' | 'ingredients' | 'composition' | null
const actionsClusterRef = ref(null);
const nutritionButtonRef = ref(null);
const ingredientsButtonRef = ref(null);
const compositionButtonRef = ref(null);
const { tooltipRef, tooltipStyle, openAt, close: hideFloatingTooltip } = useFixedTooltip();

const liveMessage = ref("");
let liveMessageTimer = null;
const justAddedToCart = ref(false);
const justToggledFav = ref(false);
const justChangedQty = ref(false);
const justPressedNutrition = ref(false);
const justPressedIngredients = ref(false);
let justAddedTimer = null;
let justFavTimer = null;
let justQtyTimer = null;
let justNutritionTimer = null;
let justIngredientsTimer = null;

function setLiveMessage(message) {
    liveMessage.value = message;
    if (liveMessageTimer) clearTimeout(liveMessageTimer);
    liveMessageTimer = setTimeout(() => {
        liveMessage.value = "";
    }, 900);
}

const FEEDBACK_ANIM_MS = 780;

function pulseAddedToCart() {
    justAddedToCart.value = true;
    if (justAddedTimer) clearTimeout(justAddedTimer);
    justAddedTimer = setTimeout(() => {
        justAddedToCart.value = false;
    }, FEEDBACK_ANIM_MS);
}

function pulseFav() {
    justToggledFav.value = true;
    if (justFavTimer) clearTimeout(justFavTimer);
    justFavTimer = setTimeout(() => {
        justToggledFav.value = false;
    }, FEEDBACK_ANIM_MS);
}

function pulseQty() {
    justChangedQty.value = true;
    if (justQtyTimer) clearTimeout(justQtyTimer);
    justQtyTimer = setTimeout(() => {
        justChangedQty.value = false;
    }, FEEDBACK_ANIM_MS);
}

function pulseNutritionBtn() {
    justPressedNutrition.value = true;
    if (justNutritionTimer) clearTimeout(justNutritionTimer);
    justNutritionTimer = setTimeout(() => {
        justPressedNutrition.value = false;
    }, 420);
}

function pulseIngredientsBtn() {
    justPressedIngredients.value = true;
    if (justIngredientsTimer) clearTimeout(justIngredientsTimer);
    justIngredientsTimer = setTimeout(() => {
        justPressedIngredients.value = false;
    }, 420);
}

function toggleNutritionTooltip() {
    pulseNutritionBtn();
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
    pulseIngredientsBtn();
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

let outsideClickHandler = null;
onMounted(() => {
    outsideClickHandler = (e) => {
        if (!openTooltip.value) return;

        if (actionsClusterRef.value?.contains(e.target)) {
            return;
        }
        if (tooltipRef.value?.contains(e.target)) {
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

    if (liveMessageTimer) clearTimeout(liveMessageTimer);
    if (justAddedTimer) clearTimeout(justAddedTimer);
    if (justFavTimer) clearTimeout(justFavTimer);
    if (justQtyTimer) clearTimeout(justQtyTimer);
    if (justNutritionTimer) clearTimeout(justNutritionTimer);
    if (justIngredientsTimer) clearTimeout(justIngredientsTimer);
});

function handleToggleFavorite() {
    const wasFav = isFav.value;
    toggleFavorite();
    pulseFav();
    setLiveMessage(wasFav ? "Убрано из избранного" : "Добавлено в избранное");
}

function handleAddToCart() {
    addToCart(1, cartFlyOptions());
    pulseAddedToCart();
    setLiveMessage("Добавлено в корзину");
}

function handleIncrement() {
    incrementCart(cartFlyOptions());
    pulseQty();
    setLiveMessage("Количество увеличено");
}

function handleDecrement() {
    decrementCart();
    pulseQty();
    setLiveMessage("Количество уменьшено");
}

function handlePriceClick() {
    // UX: клик по цене действует как "добавить/увеличить"
    if (qtyInCart.value === 0) {
        handleAddToCart();
        return;
    }
    handleIncrement();
}

</script>

<template>
    <article :class="m.article">
        <span :class="m.srOnlyAria" aria-live="polite">{{ liveMessage }}</span>
        <div :class="m.mediaWrap">
            <img
                v-if="primaryThumb"
                ref="productImageRef"
                :src="primaryThumb"
                :srcset="imageSrcset || undefined"
                :sizes="imageSrcset ? imageSizes : undefined"
                alt=""
                :class="m.img"
                loading="lazy"
                fetchpriority="low"
            />
            <div
                v-else
                :class="m.placeholder"
            >
                {{ cs.noPhotoText }}
            </div>

            <div :class="m.gradient" />

            <div
                :class="m.imageHit"
                aria-label="Открыть карточку товара"
                @click.stop="emit('imageClick', product)"
            />

            <div :class="m.badgesCol">
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
                    :class="m.weightPill"
                >
                    {{ product.weight }} г
                </div>
                <div
                    v-if="badgeTags.length"
                    :class="m.tagsRow"
                >
                    <span
                        v-for="tag in badgeTags"
                        :key="tag.code"
                        :class="[m.tagPill, tagToneClass(tag.color)]"
                    >
                        {{ tag.label }}
                    </span>
                </div>
            </div>

            <button
                type="button"
                :class="[
                    m.favFab,
                    isFav ? m.favFabActive : '',
                    justToggledFav ? 'scale-[1.06]' : 'scale-100',
                ]"
                aria-label="Избранное"
                @click.stop="handleToggleFavorite"
            >
                <span
                    :class="[m.feedbackRing, { 'pc-feedback-ring--active': justToggledFav }]"
                    aria-hidden="true"
                />
                <i
                    :class="[
                        m.favFabIcon,
                        isFav ? 'mdi-heart' : 'mdi-heart-outline',
                        justToggledFav ? 'scale-110' : 'scale-100',
                    ]"
                />
            </button>

            <div
                :class="m.mediaFooterGradient"
                aria-hidden="true"
            />

            <div :class="m.mediaFooterStack">
                <h3
                    :class="m.titleUnderPhoto"
                    :title="product.name"
                >
                    {{ product.name }}
                </h3>

                <div :class="m.actionsUnderPhoto">
                <div
                    ref="actionsClusterRef"
                    :class="m.actionCluster"
                >
                        <div v-if="hasNutrition && isProduct">
                            <button
                                ref="nutritionButtonRef"
                                type="button"
                                :class="[
                                    m.nutritionIconBtn,
                                    justPressedNutrition ? 'scale-110' : 'scale-100',
                                ]"
                                aria-label="Показать КБЖУ"
                                @click.stop="toggleNutritionTooltip"
                            >
                                <i class="mdi mdi-fire-circle text-xl" />
                            </button>
                        </div>

                        <div v-if="hasIngredients && isProduct">
                            <button
                                ref="ingredientsButtonRef"
                                type="button"
                                :class="[
                                    m.ingredientsIconBtn,
                                    justPressedIngredients ? 'scale-110' : 'scale-100',
                                ]"
                                aria-label="Показать состав"
                                @click.stop="toggleIngredientsTooltip"
                            >
                                <i class="mdi mdi-information-outline text-xl" />
                            </button>
                        </div>

                        <div v-if="hasSetComposition">
                            <button
                                ref="compositionButtonRef"
                                type="button"
                                :class="m.ingredientsIconBtn"
                                aria-label="Показать состав набора"
                                @click.stop="toggleCompositionTooltip"
                            >
                                <i class="mdi mdi-format-list-bulleted text-xl" />
                            </button>
                        </div>

                        <div :class="m.cartIconOuter">
                            <template v-if="qtyInCart === 0">
                                <button
                                    type="button"
                                    :class="[
                                        m.cartAddText,
                                        justAddedToCart ? 'scale-[1.06]' : 'scale-100',
                                    ]"
                                    aria-label="Добавить в корзину"
                                    @click.stop="handleAddToCart"
                                >
                                    <span
                                        :class="[m.feedbackCartRing, { 'pc-feedback-ring--active': justAddedToCart }]"
                                        aria-hidden="true"
                                    />
                                    <i
                                        :class="[
                                            m.cartAddIcon,
                                            justAddedToCart ? 'scale-110' : 'scale-100',
                                        ]"
                                    />
                                    <span>В корзину</span>
                                </button>
                            </template>
                            <div
                                v-else
                                :class="[
                                    m.qtyCluster,
                                    justChangedQty ? 'scale-[1.04]' : 'scale-100',
                                ]"
                            >
                                <button
                                    type="button"
                                    :class="m.qtyMiniBtn"
                                    aria-label="Уменьшить количество"
                                    @click.stop="handleDecrement"
                                >
                                    –
                                </button>
                                <span :class="m.qtyNum">
                                    {{ qtyInCart }}
                                </span>
                                <button
                                    type="button"
                                    :class="m.qtyMiniBtn"
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
                    :class="m.priceSide"
                    @click.stop="handlePriceClick"
                    :aria-label="qtyInCart === 0 ? 'Добавить в корзину' : 'Увеличить количество'"
                >
                    {{ formatMoneyRublesRu(product.price) }}&nbsp;₽
                </button>
                </div>
            </div>
        </div>
    </article>

    <Teleport to="body">
        <div
            v-if="openTooltip"
            ref="tooltipRef"
            :class="[
                m.teleportTooltipBase,
                openTooltip === 'nutrition'
                    ? m.teleportTooltipNutritionWidth
                    : m.teleportTooltipIngredientsWidth,
            ]"
            :style="tooltipStyle"
            role="dialog"
        >
            <div v-if="openTooltip === 'nutrition'" :class="m.teleportNutritionInner">
                <div :class="m.teleportNutritionRow">
                    <span :class="m.teleportNutritionLabel">Калории</span>
                    <span :class="m.teleportNutritionVal">{{ nutrition.calories }}</span>
                </div>
                <div :class="m.teleportNutritionRow">
                    <span :class="m.teleportNutritionLabel">Белки</span>
                    <span :class="m.teleportNutritionVal">{{ nutrition.proteins }} г</span>
                </div>
                <div :class="m.teleportNutritionRow">
                    <span :class="m.teleportNutritionLabel">Жиры</span>
                    <span :class="m.teleportNutritionVal">{{ nutrition.fats }} г</span>
                </div>
                <div :class="m.teleportNutritionRow">
                    <span :class="m.teleportNutritionLabel">Углеводы</span>
                    <span :class="m.teleportNutritionVal">{{ nutrition.carbs }} г</span>
                </div>
            </div>
            <div v-else-if="openTooltip === 'ingredients'" :class="m.teleportIngredientsInner">
                <div :class="m.teleportIngredientsHeading">
                    Состав
                </div>
                <div :class="m.teleportIngredientsBody">
                    {{ ingredientsText }}
                </div>
            </div>
            <div v-else-if="openTooltip === 'composition'" :class="m.teleportIngredientsInner">
                <div :class="m.teleportIngredientsHeading">
                    Состав набора
                </div>
                <div :class="m.teleportIngredientsBody">
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

<style scoped>
/* Плавное «сияние» без резкого mount/unmount */
.pc-feedback-ring {
    opacity: 0;
    box-shadow: none;
    transform: scale(0.92);
}

.pc-feedback-ring--active {
    animation: pc-feedback-ring 0.75s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.pc-feedback-ring--cart.pc-feedback-ring--active {
    animation: pc-feedback-ring-cart 0.78s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes pc-feedback-ring {
    0% {
        opacity: 0;
        transform: scale(0.88);
        box-shadow: 0 0 0 0 transparent;
    }
    45% {
        opacity: 1;
        transform: scale(1);
        box-shadow: 0 0 22px var(--app-accent-glow-mid);
    }
    100% {
        opacity: 0;
        transform: scale(1.05);
        box-shadow: 0 0 0 0 transparent;
    }
}

@keyframes pc-feedback-ring-cart {
    0% {
        opacity: 0;
        transform: scale(0.85);
        box-shadow: 0 0 0 0 transparent;
    }
    42% {
        opacity: 1;
        transform: scale(1);
        box-shadow: 0 0 28px var(--app-accent-glow-strong);
    }
    100% {
        opacity: 0;
        transform: scale(1.08);
        box-shadow: 0 0 0 0 transparent;
    }
}
</style>
