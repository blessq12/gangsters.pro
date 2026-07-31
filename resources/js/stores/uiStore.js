import { defineStore } from "pinia";
import { useShellStore } from "./shellStore";

const UI_STORAGE_KEY = "gangsters_ui";
const DEFAULT_DOCK_BADGES = {
    profile: 0,
    cart: 0,
    favorites: 0,
    notifications: 0,
};

const DESKTOP_MEDIA_QUERY = "(min-width: 768px)"; // Tailwind `md`
let deviceListenerAttached = false;

export const useUiStore = defineStore("ui", {
    state: () => ({
        showBottomNav: false,
        /** Home scroll: масштаб chrome dock (1 = полный, <1 = компакт при скролле). Не persist. */
        dockChromeScrollScale: 1,
        isMobileMenuOpen: false,
        deviceMode: "mobile",
        dockActiveId: null,
        dockBadges: { ...DEFAULT_DOCK_BADGES },
        /** Сигнал для CartDockPanel: запустить handleStartCheckout (не persist). */
        pendingCheckoutStart: false,
        checkoutWizardStep: "cart",
        showGiftSelectionModal: false,
        giftModalSource: null,
        giftAutoPromptDismissed: false,
        showClosedForOrdersModal: false,
        catalogSearchOpen: false,
    }),
    getters: {
        resolvedDockBadges: (state) => (cartCount = 0, favoritesCount = 0) => ({
            ...state.dockBadges,
            profile: 0,
            cart: Number(cartCount) || 0,
            favorites: Number(favoritesCount) || 0,
        }),
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(UI_STORAGE_KEY);
                if (!raw) return;

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") return;

                if (typeof parsed.showBottomNav === "boolean") {
                    this.showBottomNav = parsed.showBottomNav;
                }

                if (typeof parsed.isMobileMenuOpen === "boolean") {
                    this.isMobileMenuOpen = parsed.isMobileMenuOpen;
                }

                if ("dockActiveId" in parsed) {
                    this.dockActiveId = parsed.dockActiveId ?? null;
                    if (this.dockActiveId === "delivery") {
                        this.dockActiveId = null;
                    }
                }

                if (parsed.dockBadges && typeof parsed.dockBadges === "object") {
                    this.dockBadges = {
                        ...this.dockBadges,
                        ...parsed.dockBadges,
                    };
                }
                this.dockBadges.profile = 0;
            } catch (e) {
                console.error("Failed to init ui store from localStorage", e);
            }
        },
        persist() {
            if (typeof window === "undefined") return;

            window.localStorage.setItem(
                UI_STORAGE_KEY,
                JSON.stringify({
                    showBottomNav: this.showBottomNav,
                    isMobileMenuOpen: this.isMobileMenuOpen,
                    dockActiveId: this.dockActiveId,
                    dockBadges: this.dockBadges,
                }),
            );
        },
        setDockChromeScrollScale(value) {
            const next = Number(value);
            this.dockChromeScrollScale = Number.isFinite(next)
                ? Math.min(1, Math.max(0.5, next))
                : 1;
        },
        setShowBottomNav(value) {
            this.showBottomNav = Boolean(value);
            this.persist();
        },
        toggleBottomNav() {
            this.showBottomNav = !this.showBottomNav;
            this.persist();
        },
        setDockActive(id) {
            const shellStore = useShellStore();
            if (!shellStore.dockReady) {
                shellStore.enqueueDockOpen(id);
                return;
            }

            this.applyDockActive(id);
        },
        applyDockActive(id) {
            this.dockActiveId = this.dockActiveId === id ? null : id;
            if (this.dockActiveId) {
                this.showBottomNav = true;
                this.dockChromeScrollScale = 1;
            }
            this.persist();
        },
        /** Закрыть только панель дока; нижний бар (табы) не трогаем. */
        closeDockPanel() {
            if (this.dockActiveId === null) return;
            this.dockActiveId = null;
            this.persist();
        },
        requestCheckoutStart() {
            this.pendingCheckoutStart = true;
        },
        consumeCheckoutStart() {
            this.pendingCheckoutStart = false;
        },
        setCheckoutWizardStep(step) {
            this.checkoutWizardStep = step;
        },
        openGiftSelectionModal({ source = "manual" } = {}) {
            if (this.checkoutWizardStep !== "confirm") {
                return;
            }
            this.giftModalSource = source;
            this.showGiftSelectionModal = true;
        },
        closeGiftSelectionModal({ dismissAuto = false } = {}) {
            this.showGiftSelectionModal = false;
            if (dismissAuto) {
                this.giftAutoPromptDismissed = true;
            }
            this.giftModalSource = null;
        },
        openClosedForOrdersModal() {
            this.showClosedForOrdersModal = true;
        },
        closeClosedForOrdersModal() {
            this.showClosedForOrdersModal = false;
        },
        openCatalogSearch() {
            this.catalogSearchOpen = true;
        },
        closeCatalogSearch() {
            this.catalogSearchOpen = false;
        },
        resetGiftAutoPromptDismissed() {
            this.giftAutoPromptDismissed = false;
        },
        setMobileMenuOpen(value) {
            this.isMobileMenuOpen = Boolean(value);
            this.persist();
        },
        toggleMobileMenu() {
            this.isMobileMenuOpen = !this.isMobileMenuOpen;
            this.persist();
        },
        /**
         * Определяем вертикаль (mobile/desktop) на старте и при ресайзе.
         * Важно: boundary синхронизируем с Tailwind `md` (768px).
         */
        initDeviceMode() {
            if (typeof window === "undefined") return;
            const mq = window.matchMedia(DESKTOP_MEDIA_QUERY);

            const apply = () => {
                this.deviceMode = mq.matches ? "desktop" : "mobile";
            };

            apply();

            if (deviceListenerAttached) return;
            deviceListenerAttached = true;

            // Safari/старые браузеры: используем addListener как fallback.
            if (typeof mq.addEventListener === "function") {
                mq.addEventListener("change", apply);
                return;
            }

            if (typeof mq.addListener === "function") {
                mq.addListener(apply);
            }
        },
        setDockBadges(partial) {
            if (!partial || typeof partial !== "object") return;
            this.dockBadges = {
                ...this.dockBadges,
                ...partial,
            };
            this.dockBadges.profile = 0;
            this.persist();
        },
        resetDockBadges() {
            this.dockBadges = { ...DEFAULT_DOCK_BADGES };
            this.persist();
        },
        clear() {
            this.showBottomNav = false;
            this.dockChromeScrollScale = 1;
            this.isMobileMenuOpen = false;
            this.dockActiveId = null;
            this.dockBadges = { ...DEFAULT_DOCK_BADGES };
            this.pendingCheckoutStart = false;
            this.showGiftSelectionModal = false;
            this.giftModalSource = null;
            this.giftAutoPromptDismissed = false;
            this.showClosedForOrdersModal = false;
            this.catalogSearchOpen = false;
            if (typeof window !== "undefined") {
                window.localStorage.removeItem(UI_STORAGE_KEY);
            }
        },
    },
});

