import { computed, inject, unref } from "vue";
import { CatalogSearchActionSourceKey } from "../../features/catalog/catalogSearchContext";
import { useCheckoutSession } from "../../features/checkout/useCheckoutSession";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useFavoritesStore } from "../../stores/favoritesStore";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

export function useProductActions(productSource) {
    const checkoutStore = useCheckoutStore();
    const cartReadModel = useCheckoutSession();
    const favoritesStore = useFavoritesStore();
    const searchActionSource = inject(CatalogSearchActionSourceKey, null);
    const cartEventSource = searchActionSource ?? "catalog";

    const product = computed(() => unref(productSource) ?? null);
    const productId = computed(() => product.value?.id ?? null);

    const qtyInCart = computed(() =>
        productId.value ? cartReadModel.quantityByProduct(productId.value) : 0,
    );

    const isFav = computed(() =>
        productId.value ? favoritesStore.isFavorite(productId.value) : false,
    );

    const addToCart = async (qty = 1) => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_ADD_REQUESTED, {
            product: product.value,
            qty,
            source: cartEventSource,
        });
        await checkoutStore.addToCart(product.value, qty);
    };

    const incrementCart = async () => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_INCREMENT_REQUESTED, {
            productId: productId.value,
            source: cartEventSource,
        });
        await checkoutStore.incrementCart(productId.value);
    };

    const decrementCart = async () => {
        if (!productId.value) return;
        await checkoutStore.decrementCart(productId.value);
    };

    const toggleFavorite = () => {
        if (!productId.value) return;
        const adding = !isFav.value;
        if (adding) {
            emitDomainEvent(DOMAIN_EVENTS.FAVORITE_ADD_REQUESTED, {
                product: product.value,
                source: cartEventSource,
            });
        }
        void favoritesStore.toggleFavorite(product.value);
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
