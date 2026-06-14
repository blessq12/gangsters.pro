import { computed, unref } from "vue";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCheckoutSession } from "../../features/checkout/useCheckoutSession";
import { useFavoritesCommands, useFavoritesReadModel } from "../../features/favorites/useFavorites";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

export function useProductActions(productSource) {
    const cartCommands = useCartCommands();
    const cartReadModel = useCheckoutSession();
    const favoritesCommands = useFavoritesCommands();
    const favoritesReadModel = useFavoritesReadModel();

    const product = computed(() => unref(productSource) ?? null);
    const productId = computed(() => product.value?.id ?? null);

    const qtyInCart = computed(() =>
        productId.value ? cartReadModel.quantityByProduct(productId.value) : 0,
    );

    const isFav = computed(() =>
        productId.value ? favoritesReadModel.isFavorite(productId.value) : false,
    );

    const addToCart = async (qty = 1, fly = {}) => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_ADD_REQUESTED, {
            product: product.value,
            qty,
            source: "catalog",
            flySourceEl: fly.flySourceEl ?? null,
            flyImageUrl: fly.flyImageUrl ?? null,
        });
        await cartCommands.addProductToCart(product.value, qty);
    };

    const incrementCart = async (fly = {}) => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_INCREMENT_REQUESTED, {
            productId: productId.value,
            source: "catalog",
            flySourceEl: fly.flySourceEl ?? null,
            flyImageUrl: fly.flyImageUrl ?? null,
        });
        await cartCommands.incrementProductInCart(productId.value);
    };

    const decrementCart = async () => {
        if (!productId.value) return;
        await cartCommands.decrementProductInCart(productId.value);
    };

    const toggleFavorite = () => {
        if (!productId.value) return;
        favoritesCommands.toggle(product.value);
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

