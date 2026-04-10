<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { playCatalogItemsEnter } from "../../animations/animationManager";

const props = defineProps({
    sections: {
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
    cardsPerRow: {
        type: Number,
        default: 1,
    },
    mobileCardViewMode: {
        type: String,
        default: "grid", // grid | horizontal
    },
});

const emit = defineEmits(["productImageClick"]);

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

const animateGrid = async () => {
    await nextTick();
    if (!containerRef.value) return;
    playCatalogItemsEnter(containerRef.value);
};

onMounted(() => {
    animateGrid();
});

watch(
    () => props.sections,
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

        <div v-else-if="!sections.length" class="text-sm text-slate-500">
            {{ emptyMessage }}
        </div>

        <div
            v-else
            ref="containerRef"
            class="space-y-7"
        >
            <section v-for="section in sections" :key="section.id ?? section.name">
                <h3
                    class="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-200/90"
                >
                    {{ section.name }}
                </h3>

                <div class="catalog-grid" :class="gridClass">
                    <div
                        v-for="product in section.products"
                        :key="product.id || product.sku"
                        class="catalog-item"
                    >
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
                    </div>
                </div>
            </section>
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

