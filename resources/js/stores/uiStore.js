import { defineStore } from "pinia";

const UI_STORAGE_KEY = "gangsters_ui";
const DEFAULT_DOCK_BADGES = {
    profile: 0,
    cart: 0,
    favorites: 0,
    delivery: 0,
    notifications: 0,
};

const DESKTOP_MEDIA_QUERY = "(min-width: 768px)"; // Tailwind `md`
let deviceListenerAttached = false;

export const useUiStore = defineStore("ui", {
    state: () => ({
        showBottomNav: false,
        /** Mobile: скрыть хром dock при скролле (не persist). На home игнорируется при cartTotalItems > 0. */
        mobileDockSuppressedByScroll: false,
        isMobileMenuOpen: false,
        deviceMode: "mobile",
        dockActiveId: null,
        dockBadges: { ...DEFAULT_DOCK_BADGES },
        /** Сигнал для CartDockPanel: запустить handleStartCheckout (не persist). */
        pendingCheckoutStart: false,
    }),
    getters: {
        resolvedDockBadges: (state) => (cartCount = 0, favoritesCount = 0) => ({
            ...state.dockBadges,
            profile: 0,
            delivery: 0,
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
        setMobileDockScrollSuppressed(value) {
            this.mobileDockSuppressedByScroll = Boolean(value);
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
            this.dockActiveId = this.dockActiveId === id ? null : id;
            if (this.dockActiveId) {
                this.showBottomNav = true;
                this.mobileDockSuppressedByScroll = false;
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
            this.mobileDockSuppressedByScroll = false;
            this.isMobileMenuOpen = false;
            this.dockActiveId = null;
            this.dockBadges = { ...DEFAULT_DOCK_BADGES };
            if (typeof window !== "undefined") {
                window.localStorage.removeItem(UI_STORAGE_KEY);
            }
        },
    },
});

