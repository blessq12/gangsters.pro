import { onMounted, onUnmounted, unref, watch } from "vue";
import { prefersReducedMotion } from "../../animations/animationManager";

const SCROLL_STOP_MS = 420;
const SCROLL_DELTA_PX = 10;
const COMPACT_SCALE = {
    mobile: 0.78,
    desktop: 0.86,
};

/**
 * Home: при любом вертикальном скролле уменьшаем chrome dock (scale), после остановки — scale 1.
 * Не зависит от корзины. Панель dock открыта — всегда scale 1.
 *
 * @param {object} options
 * @param {object} options.uiStore
 * @param {import('vue').Ref<boolean> | boolean} options.bottomBarReady
 * @param {() => boolean} options.isHome
 */
export function useDockScrollScale({ uiStore, bottomBarReady, isHome }) {
    let lastScrollY = 0;
    let ticking = false;
    /** @type {ReturnType<typeof setTimeout> | null} */
    let scrollStopTimer = null;

    function compactScale() {
        return uiStore.deviceMode === "desktop"
            ? COMPACT_SCALE.desktop
            : COMPACT_SCALE.mobile;
    }

    function resetScale() {
        uiStore.setDockChromeScrollScale(1);
    }

    function scheduleScaleRestore() {
        if (scrollStopTimer) clearTimeout(scrollStopTimer);
        scrollStopTimer = setTimeout(() => {
            scrollStopTimer = null;
            resetScale();
        }, SCROLL_STOP_MS);
    }

    function applyScrollLogic() {
        if (typeof window === "undefined") return;
        if (!unref(bottomBarReady)) return;

        if (!isHome()) {
            resetScale();
            return;
        }

        if (uiStore.dockActiveId !== null) {
            resetScale();
            return;
        }

        if (prefersReducedMotion()) {
            resetScale();
            return;
        }

        const y = window.scrollY;
        const dy = y - lastScrollY;

        scheduleScaleRestore();

        if (Math.abs(dy) >= SCROLL_DELTA_PX) {
            uiStore.setDockChromeScrollScale(compactScale());
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

    function syncLastScrollY() {
        if (typeof window === "undefined") return;
        lastScrollY = window.scrollY;
    }

    onMounted(() => {
        syncLastScrollY();
        window.addEventListener("scroll", onScroll, { passive: true });
    });

    onUnmounted(() => {
        window.removeEventListener("scroll", onScroll);
        if (scrollStopTimer) clearTimeout(scrollStopTimer);
    });

    watch(
        () => isHome(),
        () => {
            syncLastScrollY();
            if (!isHome()) {
                resetScale();
            }
        },
    );

    watch(
        () => uiStore.dockActiveId,
        (id) => {
            if (id) {
                resetScale();
            }
        },
    );

    watch(
        () => unref(bottomBarReady),
        (ready) => {
            if (ready) {
                syncLastScrollY();
            }
        },
    );
}
