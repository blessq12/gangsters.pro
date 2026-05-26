<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import {
    playModalClose,
    playModalOpen,
    playProductDetailInfoEnter,
} from "../../animations/animationManager";
import { buildProductGallerySlides } from "../../utils/catalog/productMedia";
import { pushBodyScrollLock, popBodyScrollLock } from "../../utils/system/bodyScrollLock";
import { useProductActions } from "../../composables/catalog/useProductActions";
import { useSwipeDownToClose } from "../../composables/ui/useSwipeDownToClose";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    product: {
        type: Object,
        default: null,
    },
    variant: {
        type: String,
        default: "desktop", // desktop | mobile
    },
});

const emit = defineEmits(["update:modelValue"]);

const ds = useAppDesign().components.catalog.modal.shell;

const isMobile = computed(() => props.variant === "mobile");
const infoEnterDelay = computed(() => (isMobile.value ? 0.25 : 0.4));

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

const swipeEnabled = computed(() => isMobile.value && props.modelValue);
const { onTouchStart: onPanelSwipeStart, onTouchEnd: onPanelSwipeEnd } =
    useSwipeDownToClose({
        boundaryRef: panelRef,
        enabled: swipeEnabled,
        onClose: close,
    });

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
                playProductDetailInfoEnter(infoRef.value, { delay: infoEnterDelay.value });
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

const galleryImages = computed(() => buildProductGallerySlides(props.product));
const galleryZoneRef = ref(null);

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

function cartFlyFromModal() {
    const img = galleryZoneRef.value?.querySelector("img");
    const first = galleryImages.value[0];
    const flyImageUrl =
        typeof first === "string" ? first : first?.url || undefined;
    return {
        flySourceEl: img,
        flyImageUrl,
    };
}

function handleModalAddToCart() {
    addToCart(1, cartFlyFromModal());
}

function handleModalIncrement() {
    incrementCart(cartFlyFromModal());
}
</script>

<template>
    <Teleport to="body">
        <div v-if="isVisible" :class="ds.root">
            <div
                ref="backdropRef"
                :class="isMobile ? ds.backdropMobile : ds.backdrop"
                aria-hidden="true"
                @click="close"
            />

            <div
                :class="[ds.content, isMobile ? ds.contentMobile : '']"
            >
                <div :class="[ds.wrapper, isMobile ? ds.wrapperMobile : '']">
                    <div
                        ref="panelRef"
                        :class="[ds.panel, isMobile ? ds.panelMobile : '']"
                        @touchstart.passive="onPanelSwipeStart"
                        @touchend="onPanelSwipeEnd"
                    >
                        <button
                            type="button"
                            :class="[ds.closeBtn, isMobile ? ds.closeBtnMobile : '']"
                            aria-label="Закрыть"
                            @click="close"
                        >
                            <i :class="ds.closeIcon" />
                        </button>

                        <template v-if="product">
                            <div :class="ds.body">
                                <div
                                    ref="galleryZoneRef"
                                    :class="ds.mediaZone"
                                >
                                    <ProductGallerySlider
                                        :images="galleryImages"
                                        :alt="product.name"
                                    />
                                </div>
                                <div
                                    :class="isMobile ? ds.mediaOverlayMobile : ds.mediaOverlayDesktop"
                                    aria-hidden="true"
                                />
                                <div
                                    ref="infoRef"
                                    :class="[
                                        ds.infoFooterOverlay,
                                        isMobile ? ds.infoFooterOverlayMobile : '',
                                    ]"
                                >
                                    <ProductDetailInfo
                                        :product="product"
                                        :qty-in-cart="qtyInCart"
                                        :is-fav="isFav"
                                        @add-to-cart="handleModalAddToCart"
                                        @increment="handleModalIncrement"
                                        @decrement="decrementCart"
                                        @toggle-favorite="toggleFavorite"
                                    />
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <p :class="[ds.empty, isMobile ? ds.emptyMobile : '']">
                                {{ ds.emptyCopy }}
                            </p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
