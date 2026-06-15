<script setup>
import { computed } from "vue";
import { useCatalogItemDisplay } from "../../composables/catalog/useCatalogItemDisplay";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
    qtyInCart: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits([
    "add-to-cart",
    "increment",
    "decrement",
]);

const di = useAppDesign().components.catalog.modal.detailInfo;
const tagTone = useAppDesign().components.catalog.cards.shared.tagTone;

const { description } = useCatalogItemDisplay(computed(() => props.product));

const tags = computed(() => {
    const source = Array.isArray(props.product?.tags)
        ? props.product.tags
        : props.product?.raw?.tags;
    if (!Array.isArray(source)) return [];
    return source
        .map((tag) => {
            const code = String(tag?.code || "").trim();
            if (!code) return null;

            return {
                code,
                label: String(tag?.label || code).trim(),
                color: String(tag?.color || "amber").trim().toLowerCase(),
            };
        })
        .filter(Boolean);
});

function tagToneClass(color) {
    const c = String(color || "").trim().toLowerCase();
    return tagTone[c] ?? tagTone.default;
}

function handleAddToCart() {
    emit("add-to-cart");
}

function handlePriceClick() {
    if (props.qtyInCart === 0) {
        handleAddToCart();
        return;
    }
    emit("increment");
}

function handleIncrement() {
    emit("increment");
}

function handleDecrement() {
    emit("decrement");
}
</script>

<template>
    <div
        v-if="product"
        :class="di.card"
    >
        <div
            v-if="tags.length || description"
            :class="di.titleCol"
        >
            <div
                v-if="tags.length"
                :class="di.tagsRow"
            >
                <span
                    v-for="tag in tags"
                    :key="tag.code"
                    :class="[di.tagPill, tagToneClass(tag.color)]"
                >
                    {{ tag.label }}
                </span>
            </div>
            <p
                v-if="description"
                :class="di.description"
            >
                {{ description }}
            </p>
        </div>

        <div :class="di.controlsRow">
            <div :class="di.actionIsland">
                <button
                    v-if="product.price != null"
                    type="button"
                    :class="di.priceHeaderBtn"
                    :aria-label="qtyInCart === 0 ? 'Добавить в корзину' : 'Увеличить количество'"
                    @click.stop="handlePriceClick"
                >
                    {{ formatMoneyRublesRu(product.price) }}&nbsp;₽
                </button>

                <div :class="di.cartIconOuter">
                    <button
                        v-if="qtyInCart === 0"
                        type="button"
                        :class="di.cartAddIconBtn"
                        aria-label="Добавить в корзину"
                        @click.stop="handleAddToCart"
                    >
                        В корзину
                    </button>
                    <div
                        v-else
                        :class="di.qtyCluster"
                    >
                        <button
                            type="button"
                            :class="di.qtyMiniBtn"
                            aria-label="Уменьшить количество"
                            @click.stop="handleDecrement"
                        >
                            –
                        </button>
                        <span :class="di.qtyNum">
                            {{ qtyInCart }}
                        </span>
                        <button
                            type="button"
                            :class="di.qtyMiniBtn"
                            aria-label="Увеличить количество"
                            @click.stop="handleIncrement"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
