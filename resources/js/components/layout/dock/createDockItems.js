import { defineAsyncComponent } from "vue";
import { DOCK_META } from "./dockMeta";
import { DOCK_PANEL_LOADERS } from "./dockPanelLoaders";

/**
 * Единый список вкладок дока: одни и те же панели на мобиле и десктопе (responsive-разметка).
 */
export function createDockItems() {
    return DOCK_META.map((meta) => {
        const load = DOCK_PANEL_LOADERS[meta.id];
        if (!load) {
            throw new Error(`createDockItems: no loader for id "${meta.id}"`);
        }
        return {
            ...meta,
            content: defineAsyncComponent(load),
        };
    });
}
