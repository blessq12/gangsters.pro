<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import { useCartStore } from "../../stores/cartStore";
import { playModalClose, playModalOpen, playProductDetailInfoEnter } from "../../animations/animationManager";
import { buildProductGallerySlides } from "../../utils/catalog/productMedia";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../utils/system/bodyScrollLock";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
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
let bodyScrollLocksHeld = 0;

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
            if (infoRef.value) {
                playProductDetailInfoEnter(infoRef.value, { delay: 0.4 });
            }
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

const handleToggleFavorite = () => {
    if (!productId.value) return;
    cartStore.toggleFavorite(props.product);
};

const handleAddToCart = () => {
    if (!productId.value) return;
    cartStore.addToCart(props.product, 1);
};

const handleIncrement = () => {
    if (!productId.value) return;
    cartStore.incrementCart(productId.value);
};

const handleDecrement = () => {
    if (!productId.value) return;
    cartStore.decrementCart(productId.value);
};
</script>

<template>
    <Teleport to="body">
        <div v-if="isVisible" class="product-detail-modal">
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
                            <div class="product-detail-modal__body">
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
                                    <ProductDetailInfo
                                        :product="product"
                                        :qty-in-cart="qtyInCart"
                                        :is-fav="isFav"
                                        @add-to-cart="handleAddToCart"
                                        @increment="handleIncrement"
                                        @decrement="handleDecrement"
                                        @toggle-favorite="handleToggleFavorite"
                                    />
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
    padding: 1.5rem;
}

.product-detail-modal__wrapper {
    margin: auto;
    width: 100%;
    max-width: 56rem;
}

.product-detail-modal__panel {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    max-height: 85vh;
    border-radius: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(0, 0, 0, 0.3);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9);
    overflow: hidden;
}

.product-detail-modal__close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    z-index: 3;
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

.product-detail-modal__body {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.product-detail-modal__media {
    position: absolute;
    inset: 0;
    z-index: 0;
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
        transparent 50%
    );
}

.product-detail-modal__media :deep(.product-gallery) {
    width: 100%;
    height: 100%;
    min-height: 100%;
}

.product-detail-modal__info {
    position: absolute;
    left: 0.75rem;
    right: 0.75rem;
    bottom: 0.75rem;
    z-index: 2;
    min-width: 0;
    max-height: 38%;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.product-detail-modal__empty {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 2rem;
    font-size: 0.875rem;
    color: #94a3b8;
    z-index: 1;
}
</style>
