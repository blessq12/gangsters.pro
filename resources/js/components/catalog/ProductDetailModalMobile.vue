<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, onUnmounted, ref, watch } from "vue";
import {
    playModalClose,
    playModalOpen,
} from "../../animations/animationManager";
import { useCartStore } from "../../stores/cartStore";
import { buildProductGallerySlides } from "../../utils/catalog/productMedia";
import {
    getProductNutritionNumbers,
    hasProductNutrition,
} from "../../utils/catalog/productNutrition";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../utils/system/bodyScrollLock";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    focusSection: {
        type: String,
        default: null,
    },
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue"]);

const cartStore = useCartStore();

const isVisible = ref(false);
const backdropRef = ref(null);
const panelRef = ref(null);
const scrollRef = ref(null);
let bodyScrollLocksHeld = 0;

const touchStart = ref({ x: 0, y: 0 });

const SWIPE_CLOSE_MIN_DISTANCE_PX = 80;
const SWIPE_CLOSE_MAX_X_RATIO = 0.5;

function onTouchStart(e) {
    if (!props.modelValue) return;
    const t = e?.touches?.[0];
    if (!t) return;
    touchStart.value = { x: t.clientX, y: t.clientY };
}

function onTouchEnd(e) {
    if (!props.modelValue) return;
    const t = e?.changedTouches?.[0];
    if (!t) return;

    const dx = t.clientX - touchStart.value.x;
    const dy = t.clientY - touchStart.value.y;
    const absDy = Math.abs(dy);
    const absDx = Math.abs(dx);

    if (dy <= SWIPE_CLOSE_MIN_DISTANCE_PX) return;
    if (absDx >= absDy * SWIPE_CLOSE_MAX_X_RATIO) return;

    const scroller = scrollRef.value;
    if (scroller && typeof scroller.scrollTop === "number" && scroller.scrollTop > 0) {
        return;
    }

    close();
}

const lockBodyScroll = () => {
    pushBodyScrollLock();
    bodyScrollLocksHeld += 1;
};

const unlockBodyScroll = () => {
    if (bodyScrollLocksHeld === 0) return;
    popBodyScrollLock();
    bodyScrollLocksHeld -= 1;
};

const close = () => {
    emit("update:modelValue", false);
};

watch(
    () => props.modelValue,
    async (val) => {
        if (val) {
            isVisible.value = true;
            lockBodyScroll();
            await nextTick();
            playModalOpen({
                backdrop: backdropRef.value,
                card: panelRef.value,
            });
        } else if (isVisible.value) {
            playModalClose({
                backdrop: backdropRef.value,
                card: panelRef.value,
                onComplete: () => {
                    isVisible.value = false;
                    unlockBodyScroll();
                },
            });
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    while (bodyScrollLocksHeld > 0) {
        popBodyScrollLock();
        bodyScrollLocksHeld -= 1;
    }
});

const galleryImages = computed(() =>
    buildProductGallerySlides(props.product),
);

const productId = computed(() => props.product?.id ?? null);

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

const ingredientsText = computed(() =>
    ingredients.value
        .map((i) => i?.name)
        .filter(Boolean)
        .join(", "),
);

const productTitle = computed(
    () => props.product?.name || props.product?.raw?.name || "Без названия",
);

const openTooltip = ref(null);
const actionsClusterRef = ref(null);
const nutritionFocusRef = ref(null);
const ingredientsFocusRef = ref(null);

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

const FEEDBACK_ANIM_MS = 780;

function setLiveMessage(message) {
    liveMessage.value = message;
    if (liveMessageTimer) clearTimeout(liveMessageTimer);
    liveMessageTimer = setTimeout(() => {
        liveMessage.value = "";
    }, 900);
}

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
    openTooltip.value =
        openTooltip.value === "nutrition" ? null : "nutrition";
}

function toggleIngredientsTooltip() {
    pulseIngredientsBtn();
    openTooltip.value =
        openTooltip.value === "ingredients" ? null : "ingredients";
}

function closeTooltip() {
    openTooltip.value = null;
}

async function openTooltipFromFocus(section) {
    if (section === "nutrition" && !hasNutrition.value) return;
    if (section === "ingredients" && !hasIngredients.value) return;

    if (section === "nutrition") {
        nutritionFocusRef.value?.scrollIntoView({ block: "nearest", behavior: "smooth" });
    } else {
        ingredientsFocusRef.value?.scrollIntoView({ block: "nearest", behavior: "smooth" });
    }
    await nextTick();
    openTooltip.value = section;
}

watch(
    () => props.focusSection,
    async (val) => {
        if (!val) {
            closeTooltip();
            return;
        }
        await nextTick();
        if (val === "nutrition") await openTooltipFromFocus("nutrition");
        else if (val === "ingredients") await openTooltipFromFocus("ingredients");
    },
    { immediate: true },
);

let outsideClickHandler = null;
onMounted(() => {
    outsideClickHandler = (e) => {
        if (!openTooltip.value) return;
        if (actionsClusterRef.value?.contains(e.target)) {
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
    if (!productId.value) return;
    const wasFav = isFav.value;
    cartStore.toggleFavorite(props.product);
    pulseFav();
    setLiveMessage(wasFav ? "Убрано из избранного" : "Добавлено в избранное");
}

function handleAddToCart() {
    if (!productId.value) return;
    cartStore.addToCart(props.product, 1);
    pulseAddedToCart();
    setLiveMessage("Добавлено в корзину");
}

function handleIncrement() {
    if (!productId.value) return;
    cartStore.incrementCart(productId.value);
    pulseQty();
    setLiveMessage("Количество увеличено");
}

function handleDecrement() {
    if (!productId.value) return;
    cartStore.decrementCart(productId.value);
    pulseQty();
    setLiveMessage("Количество уменьшено");
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isVisible"
            class="product-detail-modal"
        >
            <div
                ref="backdropRef"
                class="product-detail-modal__backdrop"
                aria-hidden="true"
                @click="close"
            />

            <div class="product-detail-modal__content">
                <div class="product-detail-modal__wrapper">
                    <div
                        ref="panelRef"
                        class="product-detail-modal__panel"
                        @touchstart.passive="onTouchStart"
                        @touchend="onTouchEnd"
                    >
                        <template v-if="product">
                            <span class="sr-only" aria-live="polite">{{ liveMessage }}</span>

                            <div
                                ref="scrollRef"
                                class="product-detail-modal__scroll"
                            >
                                <!-- Герой: галерея + тот же визуальный язык, что у ProductCardMobile -->
                                <div class="product-detail-modal__hero">
                                    <div class="product-detail-modal__gallery-host">
                                        <ProductGallerySlider
                                            :images="galleryImages"
                                            :alt="productTitle"
                                        />
                                    </div>

                                    <div
                                        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/85 via-black/45 to-black/10"
                                    />

                                    <button
                                        type="button"
                                        class="product-detail-modal__close-ring absolute left-3 top-3 z-20 flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-black/55 text-slate-200 transition-[transform,colors,border-color] duration-300 ease-out hover:border-amber-400/50 hover:text-amber-200"
                                        aria-label="Закрыть"
                                        @click="close"
                                    >
                                        <i class="mdi mdi-close text-xl" />
                                    </button>

                                    <div
                                        v-if="product.weight"
                                        class="absolute left-3 top-14 z-10 flex flex-col gap-2.5"
                                    >
                                        <div
                                            class="inline-flex items-center rounded-full border border-white/10 bg-[rgba(0,0,0,0.75)] px-2.5 py-1 text-[10px] font-medium text-slate-100 backdrop-blur"
                                        >
                                            {{ product.weight }} г
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="absolute right-3 top-3 z-20 flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-black/55 text-slate-200 transition-[transform,box-shadow,border-color,color] duration-300 ease-out hover:border-amber-400/60 hover:text-amber-200"
                                        :class="[
                                            isFav ? 'border-amber-400/60 text-amber-200' : '',
                                            justToggledFav ? 'scale-[1.06]' : 'scale-100',
                                        ]"
                                        aria-label="Избранное"
                                        @click.stop="handleToggleFavorite"
                                    >
                                        <span
                                            class="pdm-feedback-ring pointer-events-none absolute inset-0 rounded-full ring-2 ring-amber-400/45"
                                            :class="{ 'pdm-feedback-ring--active': justToggledFav }"
                                            aria-hidden="true"
                                        />
                                        <i
                                            :class="[
                                                'mdi text-xl transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]',
                                                isFav ? 'mdi-heart' : 'mdi-heart-outline',
                                                justToggledFav ? 'scale-110' : 'scale-100',
                                            ]"
                                        />
                                    </button>

                                    <div class="absolute inset-x-3 bottom-3 z-10 flex flex-col gap-2.5">
                                        <div class="w-2/3 min-w-0">
                                            <h2
                                                class="rounded-xl bg-black/35 px-2.5 py-2 text-[13px] font-semibold leading-snug text-slate-50 line-clamp-3 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur"
                                                :title="productTitle"
                                            >
                                                {{ productTitle }}
                                            </h2>
                                        </div>

                                        <div class="flex w-full shrink-0 items-center gap-3">
                                            <div
                                                ref="actionsClusterRef"
                                                class="relative flex w-fit min-w-0 items-center gap-2.5 rounded-2xl border border-white/10 bg-black/35 px-3 py-1.5 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur"
                                            >
                                            <div
                                                v-if="openTooltip === 'nutrition'"
                                                class="absolute left-0 bottom-full z-50 mb-2 w-[190px] rounded-xl border border-white/10 bg-[rgba(0,0,0,0.95)] px-2.5 py-2.5 shadow-xl backdrop-blur"
                                                role="dialog"
                                            >
                                                <div class="space-y-1 text-[11px] text-slate-100">
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

                                            <div
                                                v-if="openTooltip === 'ingredients'"
                                                class="absolute left-0 bottom-full z-50 mb-2 w-[210px] max-h-44 overflow-y-auto rounded-xl border border-white/10 bg-[rgba(0,0,0,0.95)] px-2.5 py-2.5 shadow-xl backdrop-blur"
                                                role="dialog"
                                            >
                                                <div class="space-y-1 text-[11px] text-slate-100">
                                                    <div class="text-[10px] font-medium text-slate-300">
                                                        Состав
                                                    </div>
                                                    <div class="text-slate-200/90">
                                                        {{ ingredientsText }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="hasNutrition">
                                                <button
                                                    ref="nutritionFocusRef"
                                                    type="button"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-amber-400/40 bg-black/55 text-amber-200 transition-transform duration-300 ease-out hover:border-amber-400/70 hover:text-amber-200"
                                                    :class="justPressedNutrition ? 'scale-110' : 'scale-100'"
                                                    aria-label="Показать КБЖУ"
                                                    @click.stop="toggleNutritionTooltip"
                                                >
                                                    <i class="mdi mdi-fire-circle text-xl" />
                                                </button>
                                            </div>

                                            <div v-if="hasIngredients">
                                                <button
                                                    ref="ingredientsFocusRef"
                                                    type="button"
                                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/55 text-slate-200 transition-transform duration-300 ease-out hover:border-amber-400/50 hover:text-amber-200"
                                                    :class="justPressedIngredients ? 'scale-110' : 'scale-100'"
                                                    aria-label="Показать состав"
                                                    @click.stop="toggleIngredientsTooltip"
                                                >
                                                    <i class="mdi mdi-information-outline text-xl" />
                                                </button>
                                            </div>

                                            <div class="flex h-10 shrink-0 items-center">
                                                <template v-if="qtyInCart === 0">
                                                    <button
                                                        type="button"
                                                        class="relative flex h-10 w-10 items-center justify-center rounded-full bg-amber-400 text-black transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                                        :class="justAddedToCart ? 'scale-[1.06]' : 'scale-100'"
                                                        aria-label="Добавить в корзину"
                                                        @click.stop="handleAddToCart"
                                                    >
                                                        <span
                                                            class="pdm-feedback-ring pdm-feedback-ring--cart pointer-events-none absolute -inset-1 rounded-full ring-2 ring-amber-300/55"
                                                            :class="{ 'pdm-feedback-ring--active': justAddedToCart }"
                                                            aria-hidden="true"
                                                        />
                                                        <i
                                                            :class="[
                                                                'mdi mdi-cart-outline text-xl transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]',
                                                                justAddedToCart ? 'scale-110' : 'scale-100',
                                                            ]"
                                                        />
                                                    </button>
                                                </template>
                                                <div
                                                    v-else
                                                    class="flex h-10 items-center gap-0.5 rounded-full border border-amber-400/50 bg-black/60 px-0.5 transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                                    :class="justChangedQty ? 'scale-[1.04]' : 'scale-100'"
                                                >
                                                    <button
                                                        type="button"
                                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-black/50 text-base font-semibold leading-none text-slate-100 transition-colors hover:bg-black/70"
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
                                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-black/50 text-base font-semibold leading-none text-slate-100 transition-colors hover:bg-black/70"
                                                        aria-label="Увеличить количество"
                                                        @click.stop="handleIncrement"
                                                    >
                                                        +
                                                    </button>
                                                </div>
                                            </div>
                                            </div>

                                            <div
                                                v-if="product.price != null"
                                                class="ml-auto flex min-h-10 shrink-0 items-center whitespace-nowrap rounded-lg bg-amber-400 px-3 py-1.5 text-[12px] font-semibold text-black"
                                            >
                                                {{ product.price }}&nbsp;₽
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <p class="product-detail-modal__empty">
                                Нет данных о товаре.
                            </p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.product-detail-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-detail-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
}

.product-detail-modal__content {
    position: relative;
    z-index: 1;
    width: 100%;
    max-height: 100vh;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.6rem;
}

.product-detail-modal__wrapper {
    margin: auto;
    width: 100%;
    max-width: 28rem;
}

.product-detail-modal__panel {
    position: relative;
    width: 100%;
    max-height: 97vh;
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(31, 31, 35, 0.65);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.92);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.product-detail-modal__scroll {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

.product-detail-modal__hero {
    position: relative;
    width: 100%;
    aspect-ratio: 3 / 4;
    min-height: min(72vh, 36rem);
    overflow: hidden;
}

.product-detail-modal__gallery-host {
    position: absolute;
    inset: 0;
}

.product-detail-modal__gallery-host :deep(.product-gallery) {
    width: 100%;
    height: 100% !important;
    min-height: 100% !important;
}

.product-detail-modal__gallery-host :deep(.product-gallery__swiper),
.product-detail-modal__gallery-host :deep(.swiper) {
    height: 100% !important;
}

.product-detail-modal__empty {
    margin: 0;
    padding: 2rem;
    font-size: 0.875rem;
    color: #94a3b8;
}

/* Плавный фидбек кнопок (как на карточке) */
.pdm-feedback-ring {
    opacity: 0;
    box-shadow: none;
    transform: scale(0.92);
}

.pdm-feedback-ring--active {
    animation: pdm-feedback-ring 0.75s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.pdm-feedback-ring--cart.pdm-feedback-ring--active {
    animation: pdm-feedback-ring-cart 0.78s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes pdm-feedback-ring {
    0% {
        opacity: 0;
        transform: scale(0.88);
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0);
    }
    45% {
        opacity: 1;
        transform: scale(1);
        box-shadow: 0 0 22px rgba(251, 191, 36, 0.45);
    }
    100% {
        opacity: 0;
        transform: scale(1.05);
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0);
    }
}

@keyframes pdm-feedback-ring-cart {
    0% {
        opacity: 0;
        transform: scale(0.85);
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0);
    }
    42% {
        opacity: 1;
        transform: scale(1);
        box-shadow: 0 0 28px rgba(251, 191, 36, 0.55);
    }
    100% {
        opacity: 0;
        transform: scale(1.08);
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0);
    }
}
</style>
