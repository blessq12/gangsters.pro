import { ref, watch, onBeforeUnmount, unref } from "vue";
import {
    pushBodyScrollLock,
    popBodyScrollLock,
} from "../../../../utils/system/bodyScrollLock";

const SWIPE_CLOSE_MIN_DISTANCE_PX = 80;
const SWIPE_CLOSE_MAX_X_RATIO = 0.5;
const SCROLL_TOP_EPSILON_PX = 2;

const DOCK_INTERACTIVE_TOUCH_SELECTOR =
    "input, label, button, textarea, select, a, [role='button']";

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

function findPrimaryPanelScroller(boundary) {
    if (!(boundary instanceof HTMLElement)) return null;

    const fromTouch = boundary.querySelector("[data-dock-panel-scroll]");
    if (fromTouch instanceof HTMLElement && isScrollableY(fromTouch)) {
        return fromTouch;
    }

    const overflowAuto = boundary.querySelectorAll("*");
    for (const el of overflowAuto) {
        if (el instanceof HTMLElement && isScrollableY(el)) {
            return el;
        }
    }

    return null;
}

function resolvePanelScroller(touchTargetEl, boundary) {
    const fromTouch = findScrollableAncestorFrom(touchTargetEl, boundary);
    if (fromTouch) {
        return fromTouch;
    }
    return findPrimaryPanelScroller(boundary);
}

function scrollTopChangedDuringGesture(start, end) {
    return Math.abs(end - start) > SCROLL_TOP_EPSILON_PX;
}

/**
 * Свайп закрытия панели и body scroll lock только для мобильного дока.
 * @param {import('pinia').Store} uiStore — ui store с dockActiveId
 * @param {import('vue').Ref<boolean> | boolean | (() => boolean)} enabled — когда false, хуки no-op
 * @param {() => void} [onRequestDismiss] — политика dismiss (клик вне / подтверждение)
 */
export function useDockMobileInteractions(uiStore, enabled, onRequestDismiss) {
    const dockPanelOuterRef = ref(null);
    const touchStart = ref({ x: 0, y: 0 });
    let touchStartTargetEl = null;
    let scrollTopAtTouchStart = 0;
    let panelScrollerAtTouchStart = null;

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

        const boundary = dockPanelOuterRef.value;
        panelScrollerAtTouchStart = resolvePanelScroller(
            touchStartTargetEl,
            boundary,
        );
        scrollTopAtTouchStart = panelScrollerAtTouchStart?.scrollTop ?? 0;
    }

    function onDockPanelTouchEnd(e) {
        if (!isOn() || !uiStore.dockActiveId) return;
        const t = e?.changedTouches?.[0];
        if (!t) return;

        const startEl = elementFromTouchTarget(touchStartTargetEl);
        if (
            startEl instanceof HTMLElement &&
            startEl.closest(DOCK_INTERACTIVE_TOUCH_SELECTOR)
        ) {
            touchStartTargetEl = null;
            panelScrollerAtTouchStart = null;
            return;
        }

        const dx = t.clientX - touchStart.value.x;
        const dy = t.clientY - touchStart.value.y;
        const absDy = Math.abs(dy);
        const absDx = Math.abs(dx);

        if (dy <= SWIPE_CLOSE_MIN_DISTANCE_PX) return;
        if (absDx >= absDy * SWIPE_CLOSE_MAX_X_RATIO) return;

        const boundary = dockPanelOuterRef.value;
        const scroller =
            panelScrollerAtTouchStart ??
            resolvePanelScroller(touchStartTargetEl, boundary);

        if (scroller) {
            const scrollTopEnd = scroller.scrollTop;
            if (scrollTopEnd > SCROLL_TOP_EPSILON_PX) {
                touchStartTargetEl = null;
                panelScrollerAtTouchStart = null;
                return;
            }
            if (
                scrollTopChangedDuringGesture(
                    scrollTopAtTouchStart,
                    scrollTopEnd,
                )
            ) {
                touchStartTargetEl = null;
                panelScrollerAtTouchStart = null;
                return;
            }
        }

        touchStartTargetEl = null;
        panelScrollerAtTouchStart = null;
        if (typeof onRequestDismiss === "function") {
            onRequestDismiss();
            return;
        }
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
