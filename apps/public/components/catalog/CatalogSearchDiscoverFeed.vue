<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import CatalogSearchResults from "./CatalogSearchResults.vue";

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    scrollRoot: {
        type: Object,
        default: null,
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
    sectionTitle: {
        type: String,
        default: "Пока ищешь",
    },
});

const emit = defineEmits(["loadMore", "productImageClick"]);

const cs = useAppDesign().components.catalog.search;
const sentinelRef = ref(null);
/** @type {IntersectionObserver | null} */
let observer = null;

function disconnectObserver() {
    observer?.disconnect();
    observer = null;
}

function connectObserver() {
    disconnectObserver();

    const sentinel = sentinelRef.value;
    if (!sentinel) {
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                emit("loadMore");
            }
        },
        {
            root: props.scrollRoot ?? null,
            rootMargin: "240px 0px",
            threshold: 0,
        },
    );

    observer.observe(sentinel);
}

onMounted(() => {
    connectObserver();
});

onBeforeUnmount(() => {
    disconnectObserver();
});

watch(
    () => [props.scrollRoot, props.products.length],
    () => {
        connectObserver();
    },
);
</script>

<template>
    <section :class="cs.discoverSection">
        <p :class="cs.discoverTitle">
            {{ sectionTitle }}
        </p>

        <CatalogSearchResults
            :products="products"
            :loading="false"
            :variant="variant"
            :cards-per-row="cardsPerRow"
            :mobile-card-view-mode="mobileCardViewMode"
            enter-animation="append"
            @product-image-click="emit('productImageClick', $event)"
        />

        <div
            ref="sentinelRef"
            :class="cs.discoverSentinel"
            aria-hidden="true"
        />
    </section>
</template>
