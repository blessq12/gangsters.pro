import { computed, unref } from "vue";
import { useCartStore } from "../../stores/cartStore";
import { useFavoritesStore } from "../../stores/favoritesStore";

export function useProductActions(productSource) {
    const cartStore = useCartStore();
    const favoritesStore = useFavoritesStore();

    const product = computed(() => unref(productSource) ?? null);
    const productId = computed(() => product.value?.id ?? null);

    const qtyInCart = computed(() =>
        productId.value ? cartStore.cartQuantityByProduct(productId.value) : 0,
    );

    const isFav = computed(() =>
        productId.value ? favoritesStore.isFavorite(productId.value) : false,
    );

    const addToCart = (qty = 1) => {
        if (!productId.value) return;
        cartStore.addToCart(product.value, qty);
    };

    const incrementCart = () => {
        if (!productId.value) return;
        cartStore.incrementCart(productId.value);
    };

    const decrementCart = () => {
        if (!productId.value) return;
        cartStore.decrementCart(productId.value);
    };

    const toggleFavorite = () => {
        if (!productId.value) return;
        favoritesStore.toggleFavorite(product.value);
    };

    return {
        productId,
        qtyInCart,
        isFav,
        addToCart,
        incrementCart,
        decrementCart,
        toggleFavorite,
    };
}

