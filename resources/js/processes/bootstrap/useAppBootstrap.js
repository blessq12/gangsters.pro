import { useCatalogStore } from "../../stores/catalogStore";
import { useFavoritesStore } from "../../stores/favoritesStore";
import { useUiStore } from "../../stores/uiStore";
import { useUserStore } from "../../stores/userStore";
import { useCartFlyToDockAnimation } from "../../composables/cart/useCartFlyToDockAnimation";
import { useDockCartAffordance } from "../../composables/cart/useDockCartAffordance";
import { useDockBadgeFeedback } from "../../composables/ui/useDockBadgeFeedback";
import { useSessionLifecycleProcess } from "../session/useSessionLifecycleProcess";
import { useGiftAutoPromptProcess } from "../benefits/useGiftAutoPromptProcess";
import { bootstrapCheckoutSession } from "../../features/checkout/checkoutBootstrap";
import {
    bootstrapClientFavorites,
    useClientFavoritesProcess,
} from "../favorites/useClientFavoritesProcess";

let bootstrapInitialized = false;
let cleanupProcesses = [];

export function useAppBootstrap() {
    if (!bootstrapInitialized) {
        const userStore = useUserStore();
        const favoritesStore = useFavoritesStore();
        const uiStore = useUiStore();
        const catalogStore = useCatalogStore();

        userStore.initFromStorage();
        favoritesStore.initFromStorage();
        uiStore.initFromStorage();
        catalogStore.initFromStorage();

        cleanupProcesses = [
            useSessionLifecycleProcess(),
            useGiftAutoPromptProcess(),
            useClientFavoritesProcess(),
            useCartFlyToDockAnimation(),
            useDockCartAffordance(),
            useDockBadgeFeedback(),
        ];

        void bootstrapCheckoutSession();
        void bootstrapClientFavorites();

        bootstrapInitialized = true;
    }

    return {
        dispose() {
            cleanupProcesses.forEach((process) => process.dispose?.());
            cleanupProcesses = [];
            bootstrapInitialized = false;
        },
    };
}
