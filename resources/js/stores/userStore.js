import { defineStore } from "pinia";
import axios from "axios";

const USER_KEY = "gangsters_user";
const DEFAULT_DOCK_BADGES = {
    profile: 1,
    cart: 0,
    favorites: 0,
    delivery: 2,
    notifications: 4,
};

function normalizeProductSnapshot(product) {
    if (!product || typeof product !== "object") {
        return null;
    }

    return {
        id: product.id ?? null,
        name: product.name || "",
        price: Number(product.price) || 0,
        weight: product.weight ?? null,
    };
}

function normalizeCartItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((item) => {
            if (!item || typeof item !== "object") {
                return null;
            }

            const productId = item.productId ?? item.productSnapshot?.id ?? null;
            const qty = Number(item.qty) || 0;

            if (!productId || qty <= 0) {
                return null;
            }

            const snapshot = normalizeProductSnapshot({
                id: productId,
                ...(item.productSnapshot || {}),
            });

            return {
                productId,
                qty,
                productSnapshot: snapshot,
            };
        })
        .filter(Boolean);
}

function normalizeFavorites(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((item) => {
            if (typeof item === "number" || typeof item === "string") {
                return {
                    productId: item,
                    productSnapshot: normalizeProductSnapshot({
                        id: item,
                        name: `Товар #${item}`,
                    }),
                };
            }

            if (!item || typeof item !== "object") {
                return null;
            }

            const productId = item.productId ?? item.productSnapshot?.id ?? item.id ?? null;
            if (!productId) {
                return null;
            }

            return {
                productId,
                productSnapshot: normalizeProductSnapshot({
                    id: productId,
                    ...(item.productSnapshot || item),
                }),
            };
        })
        .filter(Boolean);
}

export const useUserStore = defineStore("user", {
    state: () => ({
        // Основная информация о клиенте
        profile: {
            id: null,
            name: "",
            phone: "",
            email: "",
        },
        // Токен авторизации клиента
        token: null,
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
        dockBadges: { ...DEFAULT_DOCK_BADGES },
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
        isFavorite: (state) => (id) =>
            state.favorites.some((item) => item.productId === id),
        cartTotalItems(state) {
            return state.cartItems.reduce((sum, item) => sum + item.qty, 0);
        },
        cartTotalAmount(state) {
            return state.cartItems.reduce((sum, item) => {
                return sum + (Number(item.productSnapshot?.price) || 0) * item.qty;
            }, 0);
        },
        resolvedDockBadges(state) {
            return {
                ...state.dockBadges,
                cart: this.cartTotalItems,
                favorites: state.favorites.length,
            };
        },
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

                if (parsed.token) {
                    this.setToken(parsed.token);
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
                    this.cartItems = normalizeCartItems(parsed.cartItems);
                }

                if (Array.isArray(parsed.favorites)) {
                    this.favorites = normalizeFavorites(parsed.favorites);
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
                token: this.token,
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
        setToken(token) {
            this.token = token || null;
            if (this.token) {
                axios.defaults.headers.common["Authorization"] = `Bearer ${this.token}`;
            } else {
                delete axios.defaults.headers.common["Authorization"];
            }
            this.persist();
        },
        clearAuth() {
            this.setToken(null);
            this.clear();
        },
        setProfile(partial) {
            this.profile = {
                ...this.profile,
                ...(partial || {}),
            };
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
            this.addresses = [];
            this.selectedAddressId = null;
            this.catalogSelectedCategoryId = null;
            this.catalogSelectedProduct = null;
            this.dockActiveId = null;
            this.dockBadges = { ...DEFAULT_DOCK_BADGES };
            this.cartItems = [];
            this.favorites = [];
            this.showBottomNav = false;
            this.isMobileMenuOpen = false;
            if (typeof window !== "undefined") {
                window.localStorage.removeItem(USER_KEY);
            }
        },
        // --- API-кейсы клиента ---
        async registerClient(payload) {
            const response = await axios.post("/api/client/register", payload);
            const data = response.data;

            if (data?.client) {
                this.setProfile({
                    id: data.client.id ?? null,
                    name: data.client.name ?? "",
                    phone: data.client.phone ?? "",
                    email: data.client.email ?? "",
                });
                if (Array.isArray(data.client.addresses)) {
                    this.setAddresses(data.client.addresses);
                }
            }

            if (data?.token) {
                this.setToken(data.token);
            }

            return data;
        },
        async loginClient(credentials) {
            const response = await axios.post("/api/client/login", credentials);
            const data = response.data;

            if (data?.client) {
                this.setProfile({
                    id: data.client.id ?? null,
                    name: data.client.name ?? "",
                    phone: data.client.phone ?? "",
                    email: data.client.email ?? "",
                });
                if (Array.isArray(data.client.addresses)) {
                    this.setAddresses(data.client.addresses);
                }
            }

            if (data?.token) {
                this.setToken(data.token);
            }

            return data;
        },
        async fetchClientProfile() {
            if (!this.token) return null;

            const response = await axios.get("/api/client/profile", {
                headers: {
                    Authorization: `Bearer ${this.token}`,
                },
            });
            const data = response.data;

            if (data?.client) {
                this.setProfile({
                    id: data.client.id ?? null,
                    name: data.client.name ?? "",
                    phone: data.client.phone ?? "",
                    email: data.client.email ?? "",
                });
                if (Array.isArray(data.client.addresses)) {
                    this.setAddresses(data.client.addresses);
                }
            }

            return data;
        },
        async updateClientProfile(payload) {
            const response = await axios.patch("/api/client/profile", payload, {
                headers: {
                    Authorization: `Bearer ${this.token}`,
                },
            });
            const data = response.data;

            if (data?.client) {
                this.setProfile({
                    id: data.client.id ?? null,
                    name: data.client.name ?? "",
                    phone: data.client.phone ?? "",
                    email: data.client.email ?? "",
                });
                if (Array.isArray(data.client.addresses)) {
                    this.setAddresses(data.client.addresses);
                }
            }

            return data;
        },
        async addClientAddress(payload) {
            const response = await axios.post("/api/client/addresses", payload, {
                headers: {
                    Authorization: `Bearer ${this.token}`,
                },
            });
            const data = response.data;

            if (data?.client && Array.isArray(data.client.addresses)) {
                this.setAddresses(data.client.addresses);
                if (data.client.default_address_id) {
                    this.selectAddress(data.client.default_address_id);
                }
            }

            return data;
        },
        async deleteClientAddress(addressId) {
            const response = await axios.delete(`/api/client/addresses/${addressId}`, {
                headers: {
                    Authorization: `Bearer ${this.token}`,
                },
            });
            const data = response.data;

            if (data?.client && Array.isArray(data.client.addresses)) {
                this.setAddresses(data.client.addresses);
                if (data.client.default_address_id) {
                    this.selectAddress(data.client.default_address_id);
                }
            }

            return data;
        },
        async requestPasswordReset(email) {
            const response = await axios.post("/api/client/forgot-password", {
                email,
            });
            return response.data;
        },
        async changePasswordWithToken({ token, password }) {
            const response = await axios.post("/api/client/change-password", {
                token,
                password,
            });
            return response.data;
        },
        // Корзина
        addToCart(product, qty = 1) {
            if (!product || !product.id) return;
            const id = product.id;
            const safeQty = Math.max(1, Number(qty) || 1);
            const snapshot = normalizeProductSnapshot(product);
            const existing = this.cartItems.find((i) => i.productId === id);
            if (existing) {
                existing.qty += safeQty;
                existing.productSnapshot = snapshot || existing.productSnapshot;
            } else {
                this.cartItems.push({
                    productId: id,
                    qty: safeQty,
                    productSnapshot: snapshot,
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
        removeFromCart(productId) {
            this.cartItems = this.cartItems.filter((item) => item.productId !== productId);
            this.persist();
        },
        // Избранное
        toggleFavorite(product) {
            const productId =
                typeof product === "object" ? product?.id : product;

            if (!productId) return;

            const existingIndex = this.favorites.findIndex(
                (item) => item.productId === productId,
            );

            if (existingIndex !== -1) {
                this.favorites.splice(existingIndex, 1);
            } else {
                this.favorites.push({
                    productId,
                    productSnapshot:
                        normalizeProductSnapshot(product) ||
                        normalizeProductSnapshot({
                            id: productId,
                            name: `Товар #${productId}`,
                        }),
                });
            }
            this.persist();
        },
        removeFavorite(productId) {
            this.favorites = this.favorites.filter((item) => item.productId !== productId);
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

