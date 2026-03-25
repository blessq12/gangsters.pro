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
const actionsClusterRef = ref(null);

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
    <article
        class="group flex h-full flex-col overflow-hidden rounded-2xl bg-[rgba(255,255,255,0.02)] shadow-[0_18px_45px_rgba(0,0,0,0.85)] transition duration-300 hover:-translate-y-1 hover:bg-[rgba(255,255,255,0.03)]"
    >
        <span class="sr-only" aria-live="polite">{{ liveMessage }}</span>
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

            <div
                v-if="product.weight"
                class="absolute left-3 top-3 z-10"
            >
                <div
                    class="inline-flex items-center rounded-full border border-white/10 bg-[rgba(0,0,0,0.75)] px-2.5 py-1 text-[10px] font-medium text-slate-100 backdrop-blur"
                >
                    {{ product.weight }} г
                </div>
            </div>

            <!-- Только иконки/кнопки: без “острова” с текстом -->
            <button
                type="button"
                class="absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-black/55 text-slate-200 transition-[transform,box-shadow,border-color,color] duration-300 ease-out hover:border-amber-400/60 hover:text-amber-200"
                :class="[
                    isFav ? 'border-amber-400/60 text-amber-200' : '',
                    justToggledFav ? 'scale-[1.06]' : 'scale-100',
                ]"
                aria-label="Избранное"
                @click.stop="handleToggleFavorite"
            >
                <span
                    class="pc-feedback-ring pointer-events-none absolute inset-0 rounded-full ring-2 ring-amber-400/45"
                    :class="{ 'pc-feedback-ring--active': justToggledFav }"
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
                <div class="w-2/3 min-w-0 pointer-events-none">
                    <p
                        class="rounded-xl bg-black/35 px-2.5 py-2 text-[13px] font-semibold leading-snug text-slate-50 line-clamp-3 shadow-[0_0_24px_rgba(0,0,0,0.7)] backdrop-blur"
                        :title="product.name"
                    >
                        {{ product.name }}
                    </p>
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
                                        class="pc-feedback-ring pc-feedback-ring--cart pointer-events-none absolute -inset-1 rounded-full ring-2 ring-amber-300/55"
                                        :class="{ 'pc-feedback-ring--active': justAddedToCart }"
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
    </article>
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

@keyframes pc-feedback-ring-cart {
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

