import { defineStore } from "pinia";

const USER_KEY = "gangsters_user";

export const useUserStore = defineStore("user", {
    state: () => ({
        // Основная информация о клиенте
        profile: {
            id: null,
            name: "",
            phone: "",
            email: "",
        },
        // Дополнительные данные
        bonuses: 0,
        // Выбранный адрес доставки
        selectedAddressId: null,
        // Список адресов клиента
        addresses: [],
        // Каталог: выбранная категория и товар
        catalogSelectedCategoryId: null,
        catalogSelectedProduct: null,
        // Dock: активный элемент нижнего бара
        dockActiveId: null,
        // Dock: счётчики для иконок
        dockBadges: {
            profile: 1,
            cart: 3,
            favorites: 5,
            delivery: 2,
            notifications: 4,
        },
        // Корзина и избранное
        cartItems: [],
        favorites: [],
        // UI: показывать ли нижний фиксированный навбар
        showBottomNav: false,
        // UI: мобильное меню
        isMobileMenuOpen: false,
    }),
    getters: {
        hasProfile(state) {
            return Boolean(state.profile && (state.profile.name || state.profile.phone));
        },
        selectedAddress(state) {
            if (!state.selectedAddressId) return null;
            return state.addresses.find((a) => a.id === state.selectedAddressId) || null;
        },
        cartQuantityByProduct: (state) => (id) => {
            const item = state.cartItems.find((i) => i.productId === id);
            return item ? item.qty : 0;
        },
        isFavorite: (state) => (id) => state.favorites.includes(id),
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(USER_KEY);
                if (!raw) return;

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") return;

                // Аккуратно мержим, чтобы не сломать структуру
                if (parsed.profile && typeof parsed.profile === "object") {
                    this.profile = {
                        ...this.profile,
                        ...parsed.profile,
                    };
                }

                if (typeof parsed.bonuses === "number") {
                    this.bonuses = parsed.bonuses;
                }

                if (Array.isArray(parsed.addresses)) {
                    this.addresses = parsed.addresses;
                }

                if (parsed.selectedAddressId) {
                    this.selectedAddressId = parsed.selectedAddressId;
                }

                if ("catalogSelectedCategoryId" in parsed) {
                    this.catalogSelectedCategoryId = parsed.catalogSelectedCategoryId;
                }

                if ("catalogSelectedProduct" in parsed) {
                    this.catalogSelectedProduct = parsed.catalogSelectedProduct;
                }

                if ("dockActiveId" in parsed) {
                    this.dockActiveId = parsed.dockActiveId;
                }

                if (parsed.dockBadges && typeof parsed.dockBadges === "object") {
                    this.dockBadges = {
                        ...this.dockBadges,
                        ...parsed.dockBadges,
                    };
                }

                if (Array.isArray(parsed.cartItems)) {
                    this.cartItems = parsed.cartItems;
                }

                if (Array.isArray(parsed.favorites)) {
                    this.favorites = parsed.favorites;
                }

                if (typeof parsed.showBottomNav === "boolean") {
                    this.showBottomNav = parsed.showBottomNav;
                }

                if (typeof parsed.isMobileMenuOpen === "boolean") {
                    this.isMobileMenuOpen = parsed.isMobileMenuOpen;
                }
            } catch (e) {
                // Если что-то пошло не так — просто не инициализируем из стораджа
                console.error("Failed to init user store from localStorage", e);
            }
        },
        persist() {
            if (typeof window === "undefined") return;

            const payload = {
                profile: this.profile,
                bonuses: this.bonuses,
                addresses: this.addresses,
                selectedAddressId: this.selectedAddressId,
                catalogSelectedCategoryId: this.catalogSelectedCategoryId,
                catalogSelectedProduct: this.catalogSelectedProduct,
                dockActiveId: this.dockActiveId,
                dockBadges: this.dockBadges,
                cartItems: this.cartItems,
                favorites: this.favorites,
                showBottomNav: this.showBottomNav,
                isMobileMenuOpen: this.isMobileMenuOpen,
            };

            window.localStorage.setItem(USER_KEY, JSON.stringify(payload));
        },
        setProfile(partial) {
            this.profile = {
                ...this.profile,
                ...(partial || {}),
            };
            this.persist();
        },
        setBonuses(value) {
            this.bonuses = Number.isFinite(value) ? value : 0;
            this.persist();
        },
        setAddresses(addresses) {
            this.addresses = Array.isArray(addresses) ? addresses : [];
            this.persist();
        },
        upsertAddress(address) {
            if (!address || typeof address !== "object") return;

            const id = address.id ?? Date.now();
            const idx = this.addresses.findIndex((a) => a.id === id);

            if (idx === -1) {
                this.addresses.push({ ...address, id });
            } else {
                this.addresses[idx] = { ...this.addresses[idx], ...address, id };
            }

            if (!this.selectedAddressId) {
                this.selectedAddressId = id;
            }

            this.persist();
        },
        removeAddress(id) {
            this.addresses = this.addresses.filter((a) => a.id !== id);
            if (this.selectedAddressId === id) {
                this.selectedAddressId = this.addresses[0]?.id ?? null;
            }
            this.persist();
        },
        selectAddress(id) {
            this.selectedAddressId = id;
            this.persist();
        },
        // Каталог
        setCatalogCategory(categoryId) {
            this.catalogSelectedCategoryId = categoryId ?? null;
            this.persist();
        },
        setCatalogProduct(product) {
            this.catalogSelectedProduct = product ?? null;
            this.persist();
        },
        // UI
        setShowBottomNav(value) {
            this.showBottomNav = Boolean(value);
            this.persist();
        },
        toggleBottomNav() {
            this.showBottomNav = !this.showBottomNav;
            this.persist();
        },
        setDockActive(id) {
            // клик по той же иконке закрывает контент
            this.dockActiveId = this.dockActiveId === id ? null : id;
            // если выбрали какой‑то элемент — панель точно должна быть видна
            if (this.dockActiveId) {
                this.showBottomNav = true;
            }
            this.persist();
        },
        clear() {
            this.profile = {
                id: null,
                name: "",
                phone: "",
                email: "",
            };
            this.bonuses = 0;
            this.addresses = [];
            this.selectedAddressId = null;
            this.catalogSelectedCategoryId = null;
            this.catalogSelectedProduct = null;
            this.dockActiveId = null;
            this.cartItems = [];
            this.favorites = [];
            this.showBottomNav = false;
            this.isMobileMenuOpen = false;
            if (typeof window !== "undefined") {
                window.localStorage.removeItem(USER_KEY);
            }
        },
        // Корзина
        addToCart(product, qty = 1) {
            if (!product || !product.id) return;
            const id = product.id;
            const existing = this.cartItems.find((i) => i.productId === id);
            if (existing) {
                existing.qty += qty;
            } else {
                this.cartItems.push({
                    productId: id,
                    qty,
                    productSnapshot: {
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        weight: product.weight,
                    },
                });
            }
            this.persist();
        },
        incrementCart(productId) {
            const item = this.cartItems.find((i) => i.productId === productId);
            if (!item) return;
            item.qty += 1;
            this.persist();
        },
        decrementCart(productId) {
            const idx = this.cartItems.findIndex((i) => i.productId === productId);
            if (idx === -1) return;
            const item = this.cartItems[idx];
            item.qty -= 1;
            if (item.qty <= 0) {
                this.cartItems.splice(idx, 1);
            }
            this.persist();
        },
        // Избранное
        toggleFavorite(productId) {
            if (!productId) return;
            if (this.favorites.includes(productId)) {
                this.favorites = this.favorites.filter((id) => id !== productId);
            } else {
                this.favorites.push(productId);
            }
            this.persist();
        },
        setMobileMenuOpen(value) {
            this.isMobileMenuOpen = Boolean(value);
            this.persist();
        },
        toggleMobileMenu() {
            this.isMobileMenuOpen = !this.isMobileMenuOpen;
            this.persist();
        },
    },
});

