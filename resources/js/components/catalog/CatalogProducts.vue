<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
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
    emptyMessage: {
        type: String,
        default: "Тут пока тихо. Выберите другую категорию.",
    },
});

const emit = defineEmits(["productImageClick"]);

const df = useAppDesign().components.catalog.productsFlat;

const loadingCopy = computed(() => df.loadingText);
const emptyCopy = computed(
    () => props.emptyMessage || df.emptyText,
);

const gridRef = ref(null);

const animateGrid = async () => {
    await nextTick();
    if (!gridRef.value) return;
    playCatalogItemsEnter(gridRef.value);
};

onMounted(() => {
    animateGrid();
});

watch(
    () => props.products,
    () => {
        animateGrid();
    },
    { deep: true },
);
</script>

<template>
    <div :class="df.root">
        <div v-if="loading" :class="df.loading">
            {{ loadingCopy }}
        </div>

        <div v-else-if="!products.length" :class="df.empty">
            {{ emptyCopy }}
        </div>

        <div v-else ref="gridRef" class="catalog-grid">
            <div
                v-for="(product, index) in products"
                :key="product.id || product.sku"
                class="catalog-item"
            >
                <ProductCard
                    :product="product"
                    @image-click="emit('productImageClick', $event)"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Плотная masonry-сетка на lg+: см. план каталога, геометрия здесь сознательно. */
.catalog-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}

.catalog-item {
    height: 100%;
}

@media (min-width: 640px) {
    .catalog-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .catalog-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        grid-auto-rows: minmax(260px, auto);
        grid-auto-flow: dense;
    }
    .catalog-item:nth-child(9n + 1),
    .catalog-item:nth-child(9n + 5) {
        grid-column: span 2;
    }

    .catalog-item:nth-child(9n + 3),
    .catalog-item:nth-child(9n + 8) {
        grid-row: span 2;
    }
}
</style>
