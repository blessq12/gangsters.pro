<script setup>
import { computed } from "vue";
import { useCatalogCardView } from "../../composables/catalog/useCatalogCardView";
import { useProductActions } from "../../composables/catalog/useProductActions";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["imageClick"]);

const h = useAppDesign().components.catalog.cards.horizontalMobile;
const cs = useAppDesign().components.catalog.cards.shared;

const {
    primaryThumb,
    imageSrcset,
    primaryTag,
    isSet,
    setCountLabel,
} = useCatalogCardView(computed(() => props.product));

const imageSizes = "(max-width: 640px) 40vw, 240px";

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

function handleAddToCart() {
    addToCart(1);
}

function handleIncrement() {
    incrementCart();
}

function tagToneClass(color) {
    const c = String(color || "").trim().toLowerCase();
    return cs.tagTone[c] ?? cs.tagTone.default;
}
</script>

<template>
    <article :class="h.article">
        <div
            :class="h.thumbCol"
            @click.stop="emit('imageClick', product)"
        >
            <img
                v-if="primaryThumb"
                :src="primaryThumb"
                :srcset="imageSrcset || undefined"
                :sizes="imageSrcset ? imageSizes : undefined"
                alt=""
                :class="h.thumbImg"
                loading="lazy"
                fetchpriority="low"
            />
            <div
                v-else
                :class="h.thumbPlaceholder"
            >
                {{ cs.noPhotoText }}
            </div>
            <div :class="h.thumbGradient" />
        </div>

        <div :class="h.body">
            <div :class="h.titleRow">
                <span
                    v-if="isSet"
                    :class="h.setBadgeInline"
                >
                    Набор
                </span>
                <p
                    :class="h.title"
                    :title="product.name"
                >
                    {{ product.name }}
                </p>
                <span
                    v-if="isSet && setCountLabel"
                    :class="h.weightInline"
                >
                    {{ setCountLabel }}
                </span>
                <span
                    v-else-if="product.weight"
                    :class="h.weightInline"
                >
                    {{ product.weight }}&nbsp;г
                </span>
            </div>

            <span
                v-if="primaryTag"
                :class="[h.setBadgeInline, h.primaryTagInline, tagToneClass(primaryTag.color)]"
            >
                {{ primaryTag.label }}
            </span>

            <div :class="h.actionsBar">
                <button
                    type="button"
                    :class="[h.favBtn, isFav ? h.favBtnActive : '']"
                    aria-label="Избранное"
                    @click.stop="toggleFavorite"
                >
                    <i :class="[h.favIcon, isFav ? 'mdi-heart' : 'mdi-heart-outline']" />
                </button>

                <CatalogCardCommerce
                    :price="product.price"
                    :qty-in-cart="qtyInCart"
                    variant="horizontal"
                    @add-to-cart="handleAddToCart"
                    @increment="handleIncrement"
                    @decrement="decrementCart"
                />
            </div>
        </div>
    </article>
</template>

<style scoped></style>
