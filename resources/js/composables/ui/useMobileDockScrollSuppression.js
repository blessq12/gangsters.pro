import { onMounted, onUnmounted, unref, watch } from "vue";
import { shouldAllowMobileDockScrollSuppression } from "./dockChromePolicy";

const BOTTOM_THRESHOLD_PX = 80;
const SCROLL_DELTA_PX = 14;
const TOP_REVEAL_PX = 48;

/**
 * @param {number | import('vue').Ref<number> | (() => number)} source
 * @returns {number}
 */
export function resolveCartItemCount(source) {
    if (typeof source === "function") {
        return Number(source()) || 0;
    }
    return Number(unref(source)) || 0;
}

/**
 * Mobile home: скрытие хрома дока при скролле (uiStore.mobileDockSuppressedByScroll).
 * Видимость dock = showBottomNav (layout) && !mobileDockSuppressedByScroll.
 * При cartTotalItems > 0 scroll-suppression отключён (dock всегда виден на home).
 *
 * @param {object} options
 * @param {object} options.uiStore
 * @param {import('vue').Ref<boolean> | boolean} options.bottomBarReady
 * @param {() => boolean} options.isHome
 * @param {number | import('vue').Ref<number> | (() => number)} [options.cartItemCount]
 */
export function useMobileDockScrollSuppression({
    uiStore,
    bottomBarReady,
    isHome,
    cartItemCount = () => 0,
}) {
    let lastScrollY = 0;
    let ticking = false;

    function syncLastScrollY() {
        if (typeof window === "undefined") return;
        lastScrollY = window.scrollY;
    }

    function applyScrollLogic() {
        if (typeof window === "undefined") return;
        if (!unref(bottomBarReady)) return;

        if (!isHome()) {
            uiStore.setMobileDockScrollSuppressed(false);
            return;
        }

        if (uiStore.dockActiveId !== null) {
            return;
        }

        const count = resolveCartItemCount(cartItemCount);
        if (!shouldAllowMobileDockScrollSuppression(count)) {
            uiStore.setMobileDockScrollSuppressed(false);
            lastScrollY = window.scrollY;
            return;
        }

        const y = window.scrollY;

        if (y <= TOP_REVEAL_PX) {
            uiStore.setMobileDockScrollSuppressed(false);
            lastScrollY = y;
            return;
        }

        const atBottom =
            y + window.innerHeight >=
            document.documentElement.scrollHeight - BOTTOM_THRESHOLD_PX;

        if (atBottom) {
            uiStore.setMobileDockScrollSuppressed(true);
            lastScrollY = y;
            return;
        }

        const dy = y - lastScrollY;
        if (Math.abs(dy) < SCROLL_DELTA_PX) {
            return;
        }

        if (dy > 0) {
            uiStore.setMobileDockScrollSuppressed(true);
        } else {
            uiStore.setMobileDockScrollSuppressed(false);
        }

        lastScrollY = y;
    }

    function onScroll() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            ticking = false;
            applyScrollLogic();
        });
    }

    onMounted(() => {
        syncLastScrollY();
        window.addEventListener("scroll", onScroll, { passive: true });
    });

    onUnmounted(() => {
        window.removeEventListener("scroll", onScroll);
    });

    watch(
        () => isHome(),
        () => {
            syncLastScrollY();
            if (!isHome()) {
                uiStore.setMobileDockScrollSuppressed(false);
            } else if (unref(bottomBarReady)) {
                applyScrollLogic();
            }
        },
    );

    watch(
        () => uiStore.dockActiveId,
        (id) => {
            if (id) {
                uiStore.setMobileDockScrollSuppressed(false);
            }
        },
    );

    watch(
        () => unref(bottomBarReady),
        (ready) => {
            if (ready) {
                syncLastScrollY();
                applyScrollLogic();
            }
        },
    );

    watch(
        () => resolveCartItemCount(cartItemCount),
        (count) => {
            if (count > 0) {
                uiStore.setMobileDockScrollSuppressed(false);
            }
            if (isHome() && unref(bottomBarReady)) {
                applyScrollLogic();
            }
        },
    );
}
