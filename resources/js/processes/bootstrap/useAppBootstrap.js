import { useCatalogStore } from "../../stores/catalogStore";
import { useCartStore } from "../../stores/cartStore";
import { useFavoritesStore } from "../../stores/favoritesStore";
import { useUiStore } from "../../stores/uiStore";
import { useUserStore } from "../../stores/userStore";
import { useCartFlyToDockAnimation } from "../../composables/cart/useCartFlyToDockAnimation";
import { useDockCartAffordance } from "../../composables/cart/useDockCartAffordance";
import { useDockBadgeFeedback } from "../../composables/ui/useDockBadgeFeedback";
import { useSessionLifecycleProcess } from "../session/useSessionLifecycleProcess";
import { useShoppingSessionProcess } from "../shoppingSession/useShoppingSessionProcess";
import { useBenefitsProgressProcess } from "../benefits/useBenefitsProgressProcess";
import { useGiftAutoPromptProcess } from "../benefits/useGiftAutoPromptProcess";
import { bootstrapShoppingFromApi } from "../../features/shopping/shoppingBootstrap";

let bootstrapInitialized = false;
let cleanupProcesses = [];

export function useAppBootstrap() {
    if (!bootstrapInitialized) {
        const userStore = useUserStore();
        const cartStore = useCartStore();
        const favoritesStore = useFavoritesStore();
        const uiStore = useUiStore();
        const catalogStore = useCatalogStore();

        userStore.initFromStorage();
        cartStore.initFromStorage();
        favoritesStore.initFromStorage();
        uiStore.initFromStorage();
        catalogStore.initFromStorage();

        cleanupProcesses = [
            useSessionLifecycleProcess(),
            useShoppingSessionProcess(),
            useBenefitsProgressProcess(),
            useGiftAutoPromptProcess(),
            useCartFlyToDockAnimation(),
            useDockCartAffordance(),
            useDockBadgeFeedback(),
        ];

        void bootstrapShoppingFromApi();

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
