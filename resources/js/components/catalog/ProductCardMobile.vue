<script setup>
import { computed, ref } from "vue";
import { useCatalogCardView } from "../../composables/catalog/useCatalogCardView";
import { useProductActions } from "../../composables/catalog/useProductActions";
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

const {
    primaryThumb,
    imageSrcset,
    primaryTag,
    isSet,
    setCountLabel,
} = useCatalogCardView(computed(() => props.product));

const imageSizes = "(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw";

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

const liveMessage = ref("");
let liveMessageTimer = null;
const justToggledFav = ref(false);
let justFavTimer = null;
const FEEDBACK_ANIM_MS = 780;

function setLiveMessage(message) {
    liveMessage.value = message;
    if (liveMessageTimer) clearTimeout(liveMessageTimer);
    liveMessageTimer = setTimeout(() => {
        liveMessage.value = "";
    }, 900);
}

function pulseFav() {
    justToggledFav.value = true;
    if (justFavTimer) clearTimeout(justFavTimer);
    justFavTimer = setTimeout(() => {
        justToggledFav.value = false;
    }, FEEDBACK_ANIM_MS);
}

function handleToggleFavorite() {
    const wasFav = isFav.value;
    toggleFavorite();
    pulseFav();
    setLiveMessage(wasFav ? "Убрано из избранного" : "Добавлено в избранное");
}

function handleAddToCart() {
    addToCart(1);
    setLiveMessage("Добавлено в корзину");
}

function handleIncrement() {
    incrementCart();
    setLiveMessage("Количество увеличено");
}

function handleDecrement() {
    decrementCart();
    setLiveMessage("Количество уменьшено");
}
</script>

<template>
    <article :class="m.article">
        <span
            :class="m.srOnlyAria"
            aria-live="polite"
        >{{ liveMessage }}</span>
        <div :class="m.mediaWrap">
            <img
                v-if="primaryThumb"
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

            <CatalogCardBadges
                :product="product"
                :is-set="isSet"
                :set-count-label="setCountLabel"
                :primary-tag="primaryTag"
                variant="mobileGrid"
            />

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

                <CatalogCardCommerce
                    :price="product.price"
                    :qty-in-cart="qtyInCart"
                    variant="mobileGrid"
                    @add-to-cart="handleAddToCart"
                    @increment="handleIncrement"
                    @decrement="handleDecrement"
                />
            </div>
        </div>
    </article>
</template>

<style scoped>
.pc-feedback-ring {
    opacity: 0;
    box-shadow: none;
    transform: scale(0.92);
}

.pc-feedback-ring--active {
    animation: pc-feedback-ring 0.75s cubic-bezier(0.22, 1, 0.36, 1) forwards;
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
</style>
