/**
 * Медленное соединение или режим экономии трафика.
 */
export function isSlowConnection() {
    if (typeof navigator === "undefined") {
        return false;
    }

    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (!connection) {
        return false;
    }

    if (connection.saveData) {
        return true;
    }

    const type = String(connection.effectiveType || "");
    return type === "slow-2g" || type === "2g" || type === "3g";
}

/**
 * @param {() => void} task
 * @param {{ timeoutMs?: number }} [options]
 */
export function scheduleIdleTask(task, { timeoutMs = 3000 } = {}) {
    if (typeof window === "undefined" || typeof task !== "function") {
        return;
    }

    if (isSlowConnection()) {
        return;
    }

    if (typeof window.requestIdleCallback === "function") {
        window.requestIdleCallback(() => task(), { timeout: timeoutMs });
        return;
    }

    window.setTimeout(task, Math.min(timeoutMs, 2000));
}

import { DOCK_PANEL_LOADERS } from "../../../components/layout/dock/dockKit";

const PRIORITY_DOCK_PANEL_IDS = ["cart", "profile"];

/**
 * Prefetch chunk'ов панелей дока в idle (не на slow connection).
 */
export function prefetchDockPanels(panelIds = Object.keys(DOCK_PANEL_LOADERS)) {
    if (typeof window === "undefined") {
        return;
    }

    for (const id of panelIds) {
        const load = DOCK_PANEL_LOADERS[id];
        if (load) {
            void load();
        }
    }
}

export function scheduleIdlePrefetchDockPanels() {
    scheduleIdleTask(() => {
        prefetchDockPanels(PRIORITY_DOCK_PANEL_IDS);
    });

    scheduleIdleTask(() => {
        prefetchDockPanels(["favorites"]);
    }, { timeoutMs: 5000 });
}

import { useCatalogStore } from "../../catalog/store";
import { useFavoritesStore } from "../../client/store/favoritesStore";
import { useUiStore } from "../store/uiStore";
import { useUserStore } from "../../client/store/userStore";
import { useDockCartAffordance } from "./dockUi";
import {
    useCompanyClosedNoticeProcess,
    useGiftAutoPromptProcess,
    useSessionLifecycleProcess,
} from "./lifecycle";
import { bootstrapCheckoutSession } from "../../checkout/application/session";
import {
    bootstrapClientFavorites,
    useClientFavoritesProcess,
} from "../../client/application/useClientFavoritesProcess";

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
            useCompanyClosedNoticeProcess(),
            useClientFavoritesProcess(),
            useDockCartAffordance(),
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
