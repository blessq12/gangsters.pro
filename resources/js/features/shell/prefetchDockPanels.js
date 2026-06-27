import { DOCK_PANEL_LOADERS } from "../../components/layout/dock/dockPanelLoaders";
import { scheduleIdleTask } from "./networkPolicy";

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
        prefetchDockPanels(["favorites", "delivery"]);
    }, { timeoutMs: 5000 });
}
