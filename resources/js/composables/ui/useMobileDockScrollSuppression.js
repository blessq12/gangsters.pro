import { onMounted, onUnmounted, unref, watch } from "vue";

const BOTTOM_THRESHOLD_PX = 80;
const SCROLL_DELTA_PX = 14;
const TOP_REVEAL_PX = 48;

/**
 * Mobile home: скрытие хрома дока при скролле вниз и у нижнего края страницы.
 * Состояние — только uiStore.mobileDockSuppressedByScroll (без persist).
 */
export function useMobileDockScrollSuppression({ uiStore, bottomBarReady, isHome }) {
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
}
