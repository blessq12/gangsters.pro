import { computed, unref } from "vue";
import { useCartReadModel } from "../../features/shoppingSession/useCartReadModel";
import { useFavoritesCommands } from "../../features/favorites/useFavoritesCommands";
import { useFavoritesReadModel } from "../../features/favorites/useFavoritesReadModel";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

export function useProductActions(productSource) {
    const cartReadModel = useCartReadModel();
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

    const addToCart = (qty = 1, fly = {}) => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_ADD_REQUESTED, {
            product: product.value,
            qty,
            source: "catalog",
            flySourceEl: fly.flySourceEl ?? null,
            flyImageUrl: fly.flyImageUrl ?? null,
        });
    };

    const incrementCart = (fly = {}) => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_INCREMENT_REQUESTED, {
            productId: productId.value,
            source: "catalog",
            flySourceEl: fly.flySourceEl ?? null,
            flyImageUrl: fly.flyImageUrl ?? null,
        });
    };

    const decrementCart = () => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_DECREMENT_REQUESTED, {
            productId: productId.value,
            source: "catalog",
        });
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

