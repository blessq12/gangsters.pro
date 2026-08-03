import { watch, onBeforeUnmount, unref } from "vue";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../../../platform/document";

/**
 * Body scroll lock for open dock panel (mobile).
 * Swipe-down dismiss removed — close via scrim / dismiss policy only.
 *
 * @param {import('pinia').Store} uiStore — ui store с dockActiveId
 * @param {import('vue').Ref<boolean> | boolean | (() => boolean)} enabled — когда false, хуки no-op
 */
export function useDockMobileInteractions(uiStore, enabled) {
    function isOn() {
        const v = typeof enabled === "function" ? enabled() : unref(enabled);
        return Boolean(v);
    }

    watch(
        () => (isOn() ? uiStore.dockActiveId : null),
        (id, prevId) => {
            if (!isOn()) return;
            if (prevId && !id) {
                popBodyScrollLock();
            } else if (!prevId && id) {
                pushBodyScrollLock();
            }
        },
        { immediate: true },
    );

    onBeforeUnmount(() => {
        if (isOn() && uiStore.dockActiveId) {
            popBodyScrollLock();
        }
    });
}
