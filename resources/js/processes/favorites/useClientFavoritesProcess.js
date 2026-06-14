import { useFavoritesStore } from "../../stores/favoritesStore";
import { useUserStore } from "../../stores/userStore";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";

let processInitialized = false;
let cleanupHandlers = [];

export async function bootstrapClientFavorites() {
    const userStore = useUserStore();
    const favoritesStore = useFavoritesStore();

    if (!userStore.token) {
        favoritesStore.initFromStorage();
        return;
    }

    try {
        await favoritesStore.syncFromServer();
    } catch (e) {
        console.error("bootstrapClientFavorites", e);
    }
}

export function useClientFavoritesProcess() {
    if (!processInitialized) {
        const favoritesStore = useFavoritesStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_IN, () => {
                void favoritesStore.mergeGuestIntoServer();
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT, () => {
                favoritesStore.restoreGuestStateAfterLogout();
            }),
        ];

        processInitialized = true;
    }

    return {
        dispose() {
            cleanupHandlers.forEach((cleanup) => cleanup());
            cleanupHandlers = [];
            processInitialized = false;
        },
    };
}
