import { useCatalogStore } from "../../stores/catalogStore";
import { useCartStore } from "../../stores/cartStore";
import { useFavoritesStore } from "../../stores/favoritesStore";
import { useThemeStore } from "../../stores/themeStore";
import { useUiStore } from "../../stores/uiStore";
import { useUserStore } from "../../stores/userStore";
import { useSessionLifecycleProcess } from "../session/useSessionLifecycleProcess";
import { useShoppingSessionProcess } from "../shoppingSession/useShoppingSessionProcess";
import { bootstrapShoppingFromApi } from "../../features/shopping/shoppingBootstrap";

let bootstrapInitialized = false;
let cleanupProcesses = [];

export function useAppBootstrap() {
    if (!bootstrapInitialized) {
        const themeStore = useThemeStore();
        const userStore = useUserStore();
        const cartStore = useCartStore();
        const favoritesStore = useFavoritesStore();
        const uiStore = useUiStore();
        const catalogStore = useCatalogStore();

        themeStore.initTheme();
        userStore.initFromStorage();
        cartStore.initFromStorage();
        favoritesStore.initFromStorage();
        uiStore.initFromStorage();
        catalogStore.initFromStorage();

        cleanupProcesses = [
            useSessionLifecycleProcess(),
            useShoppingSessionProcess(),
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
