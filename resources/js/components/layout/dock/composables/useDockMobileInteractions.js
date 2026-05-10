import { ref, watch, onBeforeUnmount, unref } from "vue";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../../../utils/system/bodyScrollLock";

const SWIPE_CLOSE_MIN_DISTANCE_PX = 80;
const SWIPE_CLOSE_MAX_X_RATIO = 0.5;

function elementFromTouchTarget(target) {
    if (!target) return null;
    return target.nodeType === Node.TEXT_NODE ? target.parentElement : target;
}

function isScrollableY(el) {
    if (!el || !(el instanceof HTMLElement)) return false;
    const style = window.getComputedStyle(el);
    const oy = style.overflowY;
    if (oy !== "auto" && oy !== "scroll") return false;
    return el.scrollHeight > el.clientHeight + 1;
}

function findScrollableAncestorFrom(startEl, boundary) {
    if (!boundary || !startEl) return null;
    let node = elementFromTouchTarget(startEl);
    while (node && node !== boundary) {
        if (node instanceof HTMLElement && isScrollableY(node)) {
            return node;
        }
        node = node.parentElement;
    }
    if (boundary instanceof HTMLElement && isScrollableY(boundary)) {
        return boundary;
    }
    return null;
}

/**
 * Свайп закрытия панели и body scroll lock только для мобильного дока.
 * @param {import('pinia').Store} uiStore — ui store с dockActiveId, closeDockPanel
 * @param {import('vue').Ref<boolean> | boolean | (() => boolean)} enabled — когда false, хуки no-op
 */
export function useDockMobileInteractions(uiStore, enabled) {
    const dockPanelOuterRef = ref(null);
    const touchStart = ref({ x: 0, y: 0 });
    let touchStartTargetEl = null;

    function isOn() {
        const v = typeof enabled === "function" ? enabled() : unref(enabled);
        return Boolean(v);
    }

    function onDockPanelTouchStart(e) {
        if (!isOn() || !uiStore.dockActiveId) return;
        const t = e?.touches?.[0];
        if (!t) return;
        touchStart.value = { x: t.clientX, y: t.clientY };
        touchStartTargetEl = e.target;
    }

    function onDockPanelTouchEnd(e) {
        if (!isOn() || !uiStore.dockActiveId) return;
        const t = e?.changedTouches?.[0];
        if (!t) return;

        const dx = t.clientX - touchStart.value.x;
        const dy = t.clientY - touchStart.value.y;
        const absDy = Math.abs(dy);
        const absDx = Math.abs(dx);

        if (dy <= SWIPE_CLOSE_MIN_DISTANCE_PX) return;
        if (absDx >= absDy * SWIPE_CLOSE_MAX_X_RATIO) return;

        const boundary = dockPanelOuterRef.value;
        const scroller = findScrollableAncestorFrom(touchStartTargetEl, boundary);
        if (scroller && scroller.scrollTop > 0) {
            return;
        }

        touchStartTargetEl = null;
        uiStore.closeDockPanel();
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

    return {
        dockPanelOuterRef,
        onDockPanelTouchStart,
        onDockPanelTouchEnd,
    };
}
