<script setup>
import { computed, ref } from "vue";
import { useCatalogCardView } from "../../composables/catalog/useCatalogCardView";
import { useProductActions } from "../../composables/catalog/useProductActions";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["imageClick"]);

const d = useAppDesign().components.catalog.cards.desktop;
const cs = useAppDesign().components.catalog.cards.shared;

const {
    primaryThumb,
    imageSrcset,
    primaryTag,
    isSet,
    setCountLabel,
} = useCatalogCardView(computed(() => props.product));

const imageSizes = "(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw";
const productImageRef = ref(null);

const { qtyInCart, isFav, addToCart, incrementCart, decrementCart, toggleFavorite } =
    useProductActions(computed(() => props.product));

function cartFlyOptions() {
    return {
        flySourceEl: productImageRef.value,
        flyImageUrl: primaryThumb.value || undefined,
    };
}

function handleAddToCart() {
    addToCart(1, cartFlyOptions());
}

function handleIncrement() {
    incrementCart(cartFlyOptions());
}
</script>

<template>
    <article :class="d.article">
        <div :class="d.mediaWrap">
            <img
                v-if="primaryThumb"
                ref="productImageRef"
                :src="primaryThumb"
                :srcset="imageSrcset || undefined"
                :sizes="imageSrcset ? imageSizes : undefined"
                alt=""
                :class="d.img"
                loading="lazy"
                fetchpriority="low"
            />
            <div
                v-else
                :class="d.placeholder"
            >
                {{ cs.noPhotoText }}
            </div>

            <div :class="d.gradient" />

            <CatalogCardBadges
                :product="product"
                :is-set="isSet"
                :set-count-label="setCountLabel"
                :primary-tag="primaryTag"
                variant="desktop"
            />

            <div
                :class="d.imageHit"
                aria-label="Открыть карточку товара"
                @click.stop="emit('imageClick', product)"
            />

            <div
                :class="d.mediaFooterGradient"
                aria-hidden="true"
            />

            <div :class="d.mediaFooterStack">
                <div :class="d.titleRow">
                    <div :class="d.titleText">
                        <h3 :class="d.titleUnderPhoto">
                            {{ product.name }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        :class="[d.favBtn, isFav ? d.favBtnActive : '']"
                        aria-label="Избранное"
                        @click.stop="toggleFavorite"
                    >
                        <i
                            :class="[
                                'mdi',
                                isFav ? 'mdi-heart' : 'mdi-heart-outline',
                            ]"
                        />
                    </button>
                </div>

                <CatalogCardCommerce
                    :price="product.price"
                    :qty-in-cart="qtyInCart"
                    variant="desktop"
                    :compact="compact"
                    @add-to-cart="handleAddToCart"
                    @increment="handleIncrement"
                    @decrement="decrementCart"
                />
            </div>
        </div>
    </article>
</template>

<style scoped></style>
