<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import {
    playModalClose,
    playModalOpen,
} from "../../animations/animationManager";
import { useCartStore } from "../../stores/cartStore";
import { buildProductGallerySlides } from "../../utils/catalog/productMedia";

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
const infoRef = ref(null);
let savedOverflow = "";

const touchStart = ref({
    x: 0,
    y: 0,
});

const SWIPE_CLOSE_MIN_DISTANCE_PX = 80;
const SWIPE_CLOSE_MAX_X_RATIO = 0.5; // dx не должен быть слишком большим относительно dy

function onTouchStart(e) {
    // Сохраняем стартовые координаты только если модалка реально открыта
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
    const dy = t.clientY - touchStart.value.y; // вниз => dy > 0

    const absDy = Math.abs(dy);
    const absDx = Math.abs(dx);

    if (dy <= SWIPE_CLOSE_MIN_DISTANCE_PX) return; // только свайп вниз
    if (absDx >= absDy * SWIPE_CLOSE_MAX_X_RATIO) return; // слишком "кривой" свайп

    // Чтобы не закрывать модалку во время прокрутки контента:
    // если пользователь скроллит info-блок — не закрываем.
    const scroller = infoRef.value;
    if (scroller && typeof scroller.scrollTop === "number" && scroller.scrollTop > 0) {
        return;
    }

    close();
}

const lockBodyScroll = () => {
    savedOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
};

const unlockBodyScroll = () => {
    document.body.style.overflow = savedOverflow || "";
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

onBeforeUnmount(unlockBodyScroll);

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

function handleToggleFavorite() {
    if (!productId.value) return;
    cartStore.toggleFavorite(props.product);
}

function handleAddToCart() {
    if (!productId.value) return;
    cartStore.addToCart(props.product, 1);
}

function handleIncrement() {
    if (!productId.value) return;
    cartStore.incrementCart(productId.value);
}

function handleDecrement() {
    if (!productId.value) return;
    cartStore.decrementCart(productId.value);
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
                        <button
                            type="button"
                            class="product-detail-modal__close"
                            aria-label="Закрыть"
                            @click="close"
                        >
                            <i class="mdi mdi-close" />
                        </button>

                        <template v-if="product">
                            <div class="product-detail-modal__media">
                                <ProductGallerySlider
                                    :images="galleryImages"
                                    :alt="product.name"
                                />
                            </div>

                            <div
                                ref="infoRef"
                                class="product-detail-modal__info"
                            >
                                <div
                                    class="product-detail-modal__info-bg-gradient"
                                    aria-hidden="true"
                                />
                                <ProductDetailInfo
                                    :product="product"
                                    :qty-in-cart="qtyInCart"
                                    :is-fav="isFav"
                                    :focus-section="props.focusSection"
                                    @add-to-cart="handleAddToCart"
                                    @increment="handleIncrement"
                                    @decrement="handleDecrement"
                                    @toggle-favorite="handleToggleFavorite"
                                />
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
    background: rgba(0, 0, 0, 0.5);
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
    padding: 1rem;
}

.product-detail-modal__wrapper {
    margin: auto;
    width: 100%;
    max-width: 26rem;
}

.product-detail-modal__panel {
    position: relative;
    width: 100%;
    max-height: 85vh;
    border-radius: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(0, 0, 0, 0.3);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.product-detail-modal__close {
    position: absolute;
    right: 0.9rem;
    top: 0.9rem;
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(0, 0, 0, 0.5);
    color: #94a3b8;
    font-size: 1.25rem;
    transition: color 0.2s, border-color 0.2s;
}

.product-detail-modal__close:hover {
    color: #fcd34d;
    border-color: rgba(251, 191, 36, 0.5);
}

.product-detail-modal__media {
    position: relative;
    flex: 0 0 auto;
    height: min(52vh, 26rem);
    min-height: 16rem;
}

.product-detail-modal__media::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, 0.95) 0%,
        rgba(0, 0, 0, 0.4) 25%,
        transparent 55%
    );
    z-index: 2;
}

.product-detail-modal__media :deep(.product-gallery) {
    width: 100%;
    height: 100% !important;
    min-height: 100% !important;
}

.product-detail-modal__info {
    flex: 1 1 auto;
    overflow: auto;
    padding: 0.75rem 0.9rem 0.95rem;
    position: relative;
    z-index: 3;
    background: rgba(0, 0, 0, 0.92);
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(10px);
}

.product-detail-modal__info-bg-gradient {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(
        180deg,
        rgba(0, 0, 0, 0) 0%,
        rgba(0, 0, 0, 0.74) 45%,
        rgba(0, 0, 0, 0.92) 100%
    );
}

.product-detail-modal__empty {
    margin: 0;
    padding: 2rem;
    font-size: 0.875rem;
    color: #94a3b8;
}

/* Приводим “островок” info под мобильный флоу */
.product-detail-modal__info :deep(.product-detail-info) {
    justify-content: flex-start !important;
    overflow: visible !important;
    height: auto !important;
    max-height: none !important;
}

.product-detail-modal__info :deep(.product-detail-info__card) {
    max-height: none !important;
    overflow: visible !important;
    position: relative;
    z-index: 1;
}
</style>

