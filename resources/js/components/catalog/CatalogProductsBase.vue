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
    variant: {
        type: String,
        default: "mobile", // mobile | desktop
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
        <div v-if="loading" class="text-sm text-slate-400">
            Загружаем вкусняшки...
        </div>

        <div v-else-if="!products.length" class="text-sm text-slate-500">
            {{ emptyMessage }}
        </div>

        <div
            v-else
            ref="gridRef"
            class="catalog-grid"
            :class="{ 'catalog-grid--desktop': variant === 'desktop' }"
        >
            <div
                v-for="product in products"
                :key="product.id || product.sku"
                class="catalog-item"
            >
                <ProductCardMobile
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

.catalog-grid--desktop {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-auto-rows: minmax(260px, auto);
    grid-auto-flow: dense;
}

.catalog-item {
    height: 100%;
}

.catalog-grid--desktop .catalog-item:nth-child(9n + 1),
.catalog-grid--desktop .catalog-item:nth-child(9n + 5) {
    grid-column: span 2;
}

.catalog-grid--desktop .catalog-item:nth-child(9n + 3),
.catalog-grid--desktop .catalog-item:nth-child(9n + 8) {
    grid-row: span 2;
}
</style>

