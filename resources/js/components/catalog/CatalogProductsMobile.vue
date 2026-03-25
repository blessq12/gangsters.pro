<script setup>
import { nextTick, onMounted, ref, watch } from "vue";
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
    <div class="space-y-4">
        <div
            v-if="loading"
            class="text-sm text-slate-400"
        >
            Загружаем вкусняшки...
        </div>

        <div
            v-else-if="!products.length"
            class="text-sm text-slate-500"
        >
            {{ emptyMessage }}
        </div>

        <div
            v-else
            ref="gridRef"
            class="catalog-grid"
        >
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
.catalog-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}

.catalog-item {
    height: 100%;
}
</style>

