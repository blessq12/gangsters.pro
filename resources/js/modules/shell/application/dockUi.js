import { computed, onMounted, onUnmounted, ref, unref, watch } from "vue";
import { useRoute } from "vue-router";
import { prefersReducedMotion } from "../../../animations/animationManager";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../../platform/domainEvents";
import { useCheckoutStore } from "../../checkout/store";
import { useUiStore } from "../store/uiStore";

/**
 * Shell-политика видимости dock chrome (без привязки к route).
 */
export function ensureDockChromeVisible(uiStore) {
    uiStore.setShowBottomNav(true);
    uiStore.setDockChromeScrollScale(1);
}

export const DOCK_DISMISS_KIND = Object.freeze({
    IMMEDIATE: "immediate",
    CONFIRM_CHECKOUT: "confirm_checkout",
    CONFIRM_ORDER: "confirm_order",
});

const CHECKOUT_EXIT_CONFIRM = Object.freeze({
    kind: DOCK_DISMISS_KIND.CONFIRM_CHECKOUT,
    title: "Выйти из оформления?",
    message: "Введённые данные сохранятся. Продолжить покупки?",
    confirmLabel: "Продолжить покупки",
    cancelLabel: "Остаться",
});

const ORDER_CANCEL_CONFIRM = Object.freeze({
    kind: DOCK_DISMISS_KIND.CONFIRM_ORDER,
    title: "Отменить оформление?",
    message: "Заказ ещё не отправлен. Вернуться к покупкам?",
    confirmLabel: "В каталог",
    cancelLabel: "Остаться",
});

function hasText(value) {
    return String(value ?? "").trim() !== "";
}

function isGuestStepDirty(checkoutStore) {
    const guest = checkoutStore?.guestContact;
    if (!guest || typeof guest !== "object") {
        return false;
    }

    return (
        hasText(guest.name)
        || hasText(guest.phone)
        || hasText(guest.email)
    );
}

function isDeliveryStepDirty(checkoutStore) {
    const delivery = checkoutStore?.deliveryInfo;
    if (!delivery || typeof delivery !== "object") {
        return false;
    }

    if (hasText(delivery.comment)) {
        return true;
    }

    const address = delivery.address;
    if (!address || typeof address !== "object") {
        return false;
    }

    return (
        hasText(address.street)
        || hasText(address.house)
        || hasText(address.entrance)
        || hasText(address.apartment)
    );
}

function isPaymentStepDirty(checkoutStore) {
    const changeFrom = checkoutStore?.paymentInfo?.changeFrom;
    return changeFrom != null && String(changeFrom).trim() !== "";
}

function isFulfillmentStepDirty(checkoutStore) {
    return (
        isDeliveryStepDirty(checkoutStore)
        || isPaymentStepDirty(checkoutStore)
        || Boolean(checkoutStore?.paymentInfo?.method)
    );
}

/**
 * @param {{
 *   dockActiveId: string | null,
 *   checkoutWizardStep: string,
 *   checkoutStore: object,
 * }} context
 */
export function resolveDockDismissPolicy({
    dockActiveId,
    checkoutWizardStep,
    checkoutStore,
}) {
    if (!dockActiveId) {
        return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    if (dockActiveId !== "cart") {
        return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    const step = checkoutWizardStep || "cart";

    if (step === "cart" || step === "success") {
        return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    if (step === "confirm") {
        return { ...ORDER_CANCEL_CONFIRM };
    }

    if (step === "guest") {
        return isGuestStepDirty(checkoutStore)
            ? { ...CHECKOUT_EXIT_CONFIRM }
            : { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    if (step === "fulfillment") {
        return isFulfillmentStepDirty(checkoutStore)
            ? { ...CHECKOUT_EXIT_CONFIRM }
            : { kind: DOCK_DISMISS_KIND.IMMEDIATE };
    }

    return { kind: DOCK_DISMISS_KIND.IMMEDIATE };
}

export function useBottomDockState({ uiStore, cartStore, favoritesStore, dockItems }) {
    const safeDockItems = Array.isArray(dockItems) ? dockItems : [];
    const activeDockItem = computed(() =>
        safeDockItems.find((item) => item.id === uiStore.dockActiveId) || null,
    );

    const resolvedDockBadges = computed(() =>
        uiStore.resolvedDockBadges(cartStore.cartTotalItems, favoritesStore.count),
    );

    const getBadge = (id) => resolvedDockBadges.value?.[id] ?? 0;

    return {
        activeDockItem,
        getBadge,
        dockItems: safeDockItems,
    };
}


export function useDockDismiss() {
    const uiStore = useUiStore();
    const checkoutStore = useCheckoutStore();
    const pendingConfirm = ref(null);

    const isPanelOpen = computed(() => uiStore.dockActiveId !== null);

    const showScrim = computed(
        () => isPanelOpen.value && pendingConfirm.value === null,
    );

    const confirmOpen = computed({
        get() {
            return pendingConfirm.value !== null;
        },
        set(next) {
            if (!next) {
                pendingConfirm.value = null;
            }
        },
    });

    function isDismissBlocked() {
        return (
            uiStore.showGiftSelectionModal
            || uiStore.showClosedForOrdersModal
            || uiStore.catalogSearchOpen
        );
    }

    function executeDismiss() {
        uiStore.closeDockPanel();
        pendingConfirm.value = null;
    }

    function requestDockDismiss() {
        if (!uiStore.dockActiveId) {
            return;
        }

        if (isDismissBlocked()) {
            return;
        }

        const policy = resolveDockDismissPolicy({
            dockActiveId: uiStore.dockActiveId,
            checkoutWizardStep: uiStore.checkoutWizardStep,
            checkoutStore,
        });

        if (policy.kind === DOCK_DISMISS_KIND.IMMEDIATE) {
            executeDismiss();
            return;
        }

        pendingConfirm.value = policy;
    }

    function confirmDismiss() {
        executeDismiss();
    }

    function cancelDismiss() {
        pendingConfirm.value = null;
    }

    return {
        isPanelOpen,
        showScrim,
        confirmOpen,
        pendingConfirm,
        requestDockDismiss,
        confirmDismiss,
        cancelDismiss,
    };
}

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

/**
 * Home: при add из каталога — показать dock и сбросить scale до fly/add.
 */
export function useDockCartAffordance() {
    const uiStore = useUiStore();
    const route = useRoute();

    const isHome = () => route.name === "home";

    const unsubAdd = subscribeDomainEvent(
        DOMAIN_EVENTS.CART_ADD_REQUESTED,
        (payload) => {
            if (payload?.source !== "catalog") return;
            if (!isHome()) return;
            ensureDockChromeVisible(uiStore);
        },
    );

    const unsubFavAdd = subscribeDomainEvent(
        DOMAIN_EVENTS.FAVORITE_ADD_REQUESTED,
        (payload) => {
            if (payload?.source !== "catalog") return;
            if (!isHome()) return;
            ensureDockChromeVisible(uiStore);
        },
    );

    return {
        dispose() {
            unsubAdd();
            unsubFavAdd();
        },
    };
}
