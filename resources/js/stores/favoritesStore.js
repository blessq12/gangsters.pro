import { defineStore } from "pinia";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import { useUserStore } from "./userStore";
import {
    fetchClientFavoritesRequest,
    mergeGuestFavoritesRequest,
    removeClientFavoriteRequest,
    toggleClientFavoriteRequest,
} from "../api/clientApi";
import { buildMergeGuestFavoritesPayload } from "../api/clientContracts";
import { isAxiosUnauthorized } from "../utils/api/mapApiError";

export const FAVORITES_STORAGE_KEY = "gangsters_favorites";

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

function normalizeFavoritesList(list) {
    if (!Array.isArray(list)) return [];

    return list
        .map((item) => {
            if (!item || typeof item !== "object") return null;
            const productId = item.productId ?? item.productSnapshot?.id ?? null;
            if (!productId) return null;
            return {
                productId: Number(productId),
                productSnapshot: normalizeProductSnapshot({
                    id: productId,
                    ...(item.productSnapshot || {}),
                }),
            };
        })
        .filter(Boolean);
}

function readJsonLocalStorage(key) {
    if (typeof window === "undefined") return null;
    try {
        const raw = window.localStorage.getItem(key);
        if (!raw) return null;
        return JSON.parse(raw);
    } catch {
        return null;
    }
}

function isAuthenticatedClient() {
    const userStore = useUserStore();
    return Boolean(userStore.token && userStore.profile?.id);
}

export const useFavoritesStore = defineStore("favorites", {
    state: () => ({
        items: [],
        loading: false,
        error: null,
    }),
    getters: {
        favorites(state) {
            return state.items;
        },
        count(state) {
            return state.items.length;
        },
        isFavorite: (state) => (id) =>
            state.items.some((item) => Number(item.productId) === Number(id)),
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined" || isAuthenticatedClient()) {
                return;
            }

            const parsed = readJsonLocalStorage(FAVORITES_STORAGE_KEY);
            if (parsed && Array.isArray(parsed.items)) {
                this.applyLocalSnapshot(parsed.items);
            }
        },

        persistToLocalStorage() {
            if (typeof window === "undefined" || isAuthenticatedClient()) {
                return;
            }

            window.localStorage.setItem(
                FAVORITES_STORAGE_KEY,
                JSON.stringify({ items: this.items }),
            );
        },

        clearLocalStorage() {
            if (typeof window === "undefined") {
                return;
            }

            window.localStorage.removeItem(FAVORITES_STORAGE_KEY);
        },

        applyLocalSnapshot(list) {
            this.items = normalizeFavoritesList(list);
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },

        applyServerSnapshot(list) {
            this.items = normalizeFavoritesList(list);
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },

        buildTogglePayload(product) {
            if (!product || typeof product !== "object") {
                return {};
            }

            return {
                name: product.name ?? "",
                price: Number(product.price) || 0,
                weight: product.weight ?? null,
            };
        },

        toggleLocalFavorite(product) {
            const productId = typeof product === "object" ? product?.id : product;
            if (!productId) return;

            const id = Number(productId);
            const exists = this.items.some((item) => Number(item.productId) === id);

            if (exists) {
                this.items = this.items.filter((item) => Number(item.productId) !== id);
            } else {
                this.items.push({
                    productId: id,
                    productSnapshot: normalizeProductSnapshot(product),
                });
            }

            this.persistToLocalStorage();
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },

        removeLocalFavorite(productId) {
            const id = Number(productId);
            this.items = this.items.filter((item) => Number(item.productId) !== id);
            this.persistToLocalStorage();
            emitDomainEvent(DOMAIN_EVENTS.FAVORITES_CHANGED, {
                items: this.items,
            });
        },

        async syncFromServer() {
            if (!isAuthenticatedClient()) {
                this.initFromStorage();
                return;
            }

            this.loading = true;
            this.error = null;
            try {
                const data = await fetchClientFavoritesRequest();
                if (data?.favorites) {
                    this.applyServerSnapshot(data.favorites);
                }
            } catch (error) {
                if (isAxiosUnauthorized(error)) {
                    await useUserStore().clearAuth();
                    this.error = null;
                    this.initFromStorage();
                    return;
                }

                console.error("syncFromServer favorites", error);
                this.error = error?.response?.data?.message || "Не удалось загрузить избранное.";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async mergeGuestIntoServer() {
            if (!isAuthenticatedClient()) {
                return;
            }

            const guestItems = readJsonLocalStorage(FAVORITES_STORAGE_KEY)?.items;
            const localItems = Array.isArray(guestItems) && guestItems.length > 0
                ? guestItems
                : this.items;

            this.loading = true;
            this.error = null;
            try {
                if (Array.isArray(localItems) && localItems.length > 0) {
                    const data = await mergeGuestFavoritesRequest(
                        buildMergeGuestFavoritesPayload(localItems),
                    );
                    if (data?.favorites) {
                        this.applyServerSnapshot(data.favorites);
                    }
                } else {
                    await this.syncFromServer();
                }

                this.clearLocalStorage();
            } catch (error) {
                if (isAxiosUnauthorized(error)) {
                    await useUserStore().clearAuth();
                    this.error = null;
                    this.initFromStorage();
                    return;
                }

                console.error("mergeGuestIntoServer", error);
                this.error = error?.response?.data?.message || "Не удалось синхронизировать избранное.";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async toggleFavorite(product) {
            const productId = typeof product === "object" ? product?.id : product;
            if (!productId) return;

            if (!isAuthenticatedClient()) {
                this.toggleLocalFavorite(product);
                return;
            }

            this.loading = true;
            this.error = null;
            try {
                const data = await toggleClientFavoriteRequest(
                    productId,
                    this.buildTogglePayload(product),
                );
                if (data?.favorites) {
                    this.applyServerSnapshot(data.favorites);
                }
            } catch (e) {
                console.error("toggleFavorite", e);
                this.error = e?.response?.data?.message || "Не удалось обновить избранное.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async removeFavorite(productId) {
            if (!productId) return;

            if (!isAuthenticatedClient()) {
                this.removeLocalFavorite(productId);
                return;
            }

            this.loading = true;
            this.error = null;
            try {
                const data = await removeClientFavoriteRequest(productId);
                if (data?.favorites) {
                    this.applyServerSnapshot(data.favorites);
                }
            } catch (e) {
                console.error("removeFavorite", e);
                this.error = e?.response?.data?.message || "Не удалось обновить избранное.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async clear() {
            const ids = this.items.map((i) => i.productId);
            for (const id of ids) {
                await this.removeFavorite(id);
            }
        },

        restoreGuestStateAfterLogout() {
            this.$patch({ loading: false, error: null });
            this.initFromStorage();
        },
    },
});
