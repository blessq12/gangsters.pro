<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import gsap from "gsap";
import { useAppDesign } from "../../design/useAppDesign";
import { playCatalogItemsEnter } from "../../animations/animationManager";

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: "mobile",
    },
    cardsPerRow: {
        type: Number,
        default: 4,
    },
    mobileCardViewMode: {
        type: String,
        default: "grid",
    },
    enterAnimation: {
        type: String,
        default: "replace",
        validator: (value) => ["replace", "append", "none"].includes(value),
    },
});

const emit = defineEmits(["productImageClick"]);

const dp = useAppDesign().components.catalog.products;

const loadingCopy = computed(() => dp.loadingText);

const containerRef = ref(null);
const isHorizontalMobileMode = computed(
    () => props.variant === "mobile" && props.mobileCardViewMode === "horizontal",
);
const gridClass = computed(() => {
    if (props.variant === "desktop") {
        return props.cardsPerRow === 3 ? "catalog-grid--desktop-3" : "catalog-grid--desktop-4";
    }
    return "catalog-grid--mobile-1";
});
const isDesktopCardCompact = computed(
    () => props.variant === "desktop" && props.cardsPerRow === 4,
);

/** @type {ReturnType<typeof setTimeout> | null} */
let animateTimer = null;
let lastResultKey = "";
let lastAnimatedCount = 0;

function catalogItemsInContainer(container) {
    if (!container?.isConnected) return [];
    return Array.from(container.querySelectorAll(".catalog-item"));
}

function buildResultKey(products) {
    return products
        .map((product) => `${product?.kind || "product"}:${product?.id}`)
        .join("|");
}

const animateGrid = async () => {
    if (props.loading || props.enterAnimation === "none") {
        return;
    }

    await nextTick();
    const container = containerRef.value;
    if (!container?.isConnected) {
        return;
    }

    const items = catalogItemsInContainer(container);
    if (!items.length) {
        return;
    }

    const targets =
        props.enterAnimation === "append"
            ? items.slice(lastAnimatedCount)
            : items;

    if (!targets.length) {
        return;
    }

    lastAnimatedCount = items.length;
    playCatalogItemsEnter(container, { onlyItems: targets });
};

function scheduleAnimateGrid() {
    if (props.enterAnimation === "none") {
        return;
    }

    const nextKey = buildResultKey(props.products);

    if (props.enterAnimation === "append") {
        if (props.products.length <= lastAnimatedCount) {
            return;
        }
        lastResultKey = nextKey;
    } else if (nextKey === lastResultKey) {
        return;
    } else {
        lastResultKey = nextKey;
        lastAnimatedCount = 0;
    }

    if (animateTimer != null) {
        clearTimeout(animateTimer);
    }

    animateTimer = setTimeout(() => {
        animateTimer = null;
        void animateGrid();
    }, 120);
}

onBeforeUnmount(() => {
    const container = containerRef.value;
    if (container) {
        gsap.killTweensOf(catalogItemsInContainer(container));
    }
    if (animateTimer != null) {
        clearTimeout(animateTimer);
        animateTimer = null;
    }
});

watch(
    () => props.products,
    () => {
        scheduleAnimateGrid();
    },
    { deep: true, flush: "post" },
);

watch(
    () => props.loading,
    (loading) => {
        if (!loading) {
            scheduleAnimateGrid();
        }
    },
);
</script>

<template>
    <div :class="dp.root">
        <div
            v-if="loading"
            :class="dp.loading"
        >
            {{ loadingCopy }}
        </div>

        <div
            v-else
            ref="containerRef"
            class="catalog-grid"
            :class="gridClass"
        >
            <div
                v-for="product in products"
                :key="`${product.kind || 'product'}:${product.id}`"
                class="catalog-item"
            >
                <template v-if="variant === 'desktop'">
                    <ProductCard
                        :product="product"
                        :compact="isDesktopCardCompact"
                        @image-click="emit('productImageClick', $event)"
                    />
                </template>
                <template v-else>
                    <ProductCardHorizontalMobile
                        v-if="isHorizontalMobileMode"
                        :product="product"
                        @image-click="emit('productImageClick', $event)"
                    />
                    <ProductCardMobile
                        v-else
                        :product="product"
                        @image-click="emit('productImageClick', $event)"
                    />
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.catalog-grid {
    display: grid;
    gap: 1rem;
}

.catalog-grid--mobile-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.catalog-grid--desktop-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-auto-rows: minmax(260px, auto);
    gap: 1.25rem;
}

.catalog-grid--desktop-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    grid-auto-rows: minmax(260px, auto);
    gap: 1.25rem;
}

.catalog-item {
    height: 100%;
}
</style>
