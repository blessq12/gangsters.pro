import { ref, unref } from "vue";

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
 * Закрытие оверлея свайпом вниз (мобильный UX).
 * @param {object} options
 * @param {import('vue').Ref<HTMLElement|null>} options.boundaryRef — корень жеста (панель модалки)
 * @param {import('vue').Ref<boolean>|boolean|(() => boolean)} options.enabled
 * @param {() => void} options.onClose
 */
export function useSwipeDownToClose({ boundaryRef, enabled, onClose }) {
    const touchStart = ref({ x: 0, y: 0 });
    let touchStartTargetEl = null;

    function isOn() {
        const v = typeof enabled === "function" ? enabled() : unref(enabled);
        return Boolean(v);
    }

    function onTouchStart(e) {
        if (!isOn()) return;
        const t = e?.touches?.[0];
        if (!t) return;
        touchStart.value = { x: t.clientX, y: t.clientY };
        touchStartTargetEl = e.target;
    }

    function onTouchEnd(e) {
        if (!isOn()) return;
        const t = e?.changedTouches?.[0];
        if (!t) return;

        const dx = t.clientX - touchStart.value.x;
        const dy = t.clientY - touchStart.value.y;
        const absDy = Math.abs(dy);
        const absDx = Math.abs(dx);

        if (dy <= SWIPE_CLOSE_MIN_DISTANCE_PX) return;
        if (absDx >= absDy * SWIPE_CLOSE_MAX_X_RATIO) return;

        const boundary = unref(boundaryRef);
        const scroller = findScrollableAncestorFrom(touchStartTargetEl, boundary);
        if (scroller && scroller.scrollTop > 0) {
            return;
        }

        touchStartTargetEl = null;
        onClose();
    }

    return {
        onTouchStart,
        onTouchEnd,
    };
}
