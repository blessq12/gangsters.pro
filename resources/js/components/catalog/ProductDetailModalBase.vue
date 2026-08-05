<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import {
    playModalClose,
    playModalOpen,
    playProductDetailInfoEnter,
} from "../../animations/animationManager";
import { buildProductGallerySlides } from "../../modules/catalog/domain/productView";
import { pushBodyScrollLock, popBodyScrollLock } from "../../platform/document";
import { useProductActions } from "../../modules/catalog/application/models";
import { useSwipeDownToClose } from "../../modules/shell/application/dockUi";
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
const di = useAppDesign().components.catalog.modal.detailInfo;

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

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

function handleModalAddToCart() {
    addToCart(1);
}

function handleModalIncrement() {
    incrementCart();
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
                :class="[ds.content, isMobile ? ds.contentMobile : ds.contentDesktop]"
            >
                <div :class="[ds.wrapper, isMobile ? ds.wrapperMobile : '']">
                    <div
                        ref="panelRef"
                        :class="[ds.panel, isMobile ? ds.panelMobile : '']"
                        @touchstart.passive="onPanelSwipeStart"
                        @touchend="onPanelSwipeEnd"
                    >
                        <button
                            v-if="!product"
                            type="button"
                            :class="ds.closeBtn"
                            aria-label="Закрыть"
                            @click="close"
                        >
                            <i :class="ds.closeIcon" />
                        </button>

                        <template v-if="product">
                            <div :class="ds.body">
                                <div
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
                                    :class="ds.infoOverlay"
                                >
                                    <div
                                        :class="[
                                            ds.infoHeader,
                                            isMobile ? ds.infoHeaderMobile : '',
                                        ]"
                                    >
                                        <div :class="di.headerBlock">
                                            <div :class="di.titleTopRow">
                                                <button
                                                    type="button"
                                                    :class="[di.favBtn, isFav ? di.favBtnActive : '']"
                                                    aria-label="Избранное"
                                                    @click.stop="toggleFavorite"
                                                >
                                                    <i :class="[di.favIcon, isFav ? 'mdi-heart' : 'mdi-heart-outline']" />
                                                </button>
                                                <div :class="di.headerContentCol">
                                                    <h2
                                                        :class="di.title"
                                                        :title="product.name || product.raw?.name || 'Без названия'"
                                                    >
                                                        {{ product.name || product.raw?.name || "Без названия" }}
                                                    </h2>
                                                    <ProductDetailHeaderMeta :product="product" />
                                                </div>
                                                <button
                                                    type="button"
                                                    :class="ds.closeBtnInline"
                                                    aria-label="Закрыть"
                                                    @click.stop="close"
                                                >
                                                    <i :class="ds.closeIcon" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        :class="[
                                            ds.infoFooter,
                                            isMobile ? ds.infoFooterMobile : '',
                                        ]"
                                    >
                                        <ProductDetailInfo
                                            :product="product"
                                            :qty-in-cart="qtyInCart"
                                            @add-to-cart="handleModalAddToCart"
                                            @increment="handleModalIncrement"
                                            @decrement="decrementCart"
                                        />
                                    </div>
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
