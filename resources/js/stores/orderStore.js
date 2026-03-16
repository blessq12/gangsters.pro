import { defineStore } from "pinia";
import axios from "axios";

const ORDER_STORAGE_KEY = "gangsters_order_draft";

function normalizeCartItem(item) {
    if (!item || typeof item !== "object") {
        return null;
    }

    const productId =
        item.productId ??
        item.product_id ??
        item.productSnapshot?.id ??
        item.product?.id ??
        null;

    const qty = Number(item.qty ?? item.quantity ?? 0) || 0;

    if (!productId || qty <= 0) {
        return null;
    }

    const snapshotSource = item.productSnapshot || item.product || {};

    return {
        productId,
        qty,
        productSnapshot: {
            id: snapshotSource.id ?? productId,
            name: snapshotSource.name || "",
            price: Number(snapshotSource.price) || 0,
            weight: snapshotSource.weight ?? null,
        },
    };
}

function normalizeCartItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map((item) => normalizeCartItem(item))
        .filter(Boolean);
}

export const useOrderStore = defineStore("order", {
    state: () => ({
        cartItems: [],
        deliveryInfo: {
            method: "courier",
            address: null,
            comment: "",
            scheduledAt: null,
        },
        paymentInfo: {
            method: "card",
            changeFrom: null,
        },
        customerComment: "",
        orders: [],
        lastCreatedOrder: null,
        loading: {
            create: false,
            list: false,
        },
        error: {
            create: null,
            list: null,
        },
    }),
    getters: {
        cartTotalItems(state) {
            return state.cartItems.reduce((sum, item) => sum + item.qty, 0);
        },
        cartTotalAmount(state) {
            return state.cartItems.reduce((sum, item) => {
                return sum + (Number(item.productSnapshot?.price) || 0) * item.qty;
            }, 0);
        },
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(ORDER_STORAGE_KEY);
                if (!raw) return;

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") return;

                if (Array.isArray(parsed.cartItems)) {
                    this.cartItems = normalizeCartItems(parsed.cartItems);
                }

                if (parsed.deliveryInfo && typeof parsed.deliveryInfo === "object") {
                    this.deliveryInfo = {
                        ...this.deliveryInfo,
                        ...parsed.deliveryInfo,
                    };
                }

                if (parsed.paymentInfo && typeof parsed.paymentInfo === "object") {
                    this.paymentInfo = {
                        ...this.paymentInfo,
                        ...parsed.paymentInfo,
                    };
                }

                if (typeof parsed.customerComment === "string") {
                    this.customerComment = parsed.customerComment;
                }
            } catch (e) {
                console.error("Failed to init order store from localStorage", e);
            }
        },
        persistDraft() {
            if (typeof window === "undefined") return;

            const payload = {
                cartItems: this.cartItems,
                deliveryInfo: this.deliveryInfo,
                paymentInfo: this.paymentInfo,
                customerComment: this.customerComment,
            };

            window.localStorage.setItem(ORDER_STORAGE_KEY, JSON.stringify(payload));
        },
        clearDraft() {
            this.cartItems = [];
            this.deliveryInfo = {
                method: null,
                address: null,
                comment: "",
                scheduledAt: null,
            };
            this.paymentInfo = {
                method: null,
                changeFrom: null,
            };
            this.customerComment = "";

            if (typeof window !== "undefined") {
                window.localStorage.removeItem(ORDER_STORAGE_KEY);
            }
        },
        setCartItems(items) {
            this.cartItems = normalizeCartItems(items);
            this.persistDraft();
        },
        addToCart(product, qty = 1) {
            if (!product || !product.id) return;

            const id = product.id;
            const safeQty = Math.max(1, Number(qty) || 1);

            const existing = this.cartItems.find((i) => i.productId === id);
            const snapshot = {
                id: product.id,
                name: product.name || "",
                price: Number(product.price) || 0,
                weight: product.weight ?? null,
            };

            if (existing) {
                existing.qty += safeQty;
                existing.productSnapshot = snapshot;
            } else {
                this.cartItems.push({
                    productId: id,
                    qty: safeQty,
                    productSnapshot: snapshot,
                });
            }

            this.persistDraft();
        },
        updateCartItemQty(productId, qty) {
            const item = this.cartItems.find((i) => i.productId === productId);
            if (!item) return;

            const safeQty = Number(qty) || 0;
            if (safeQty <= 0) {
                this.cartItems = this.cartItems.filter((i) => i.productId !== productId);
            } else {
                item.qty = safeQty;
            }

            this.persistDraft();
        },
        removeFromCart(productId) {
            this.cartItems = this.cartItems.filter((i) => i.productId !== productId);
            this.persistDraft();
        },
        setDeliveryInfo(payload) {
            this.deliveryInfo = {
                ...this.deliveryInfo,
                ...(payload || {}),
            };
            this.persistDraft();
        },
        setPaymentInfo(payload) {
            this.paymentInfo = {
                ...this.paymentInfo,
                ...(payload || {}),
            };
            this.persistDraft();
        },
        setCustomerComment(comment) {
            this.customerComment = comment || "";
            this.persistDraft();
        },
        setOrders(orders) {
            this.orders = Array.isArray(orders) ? orders : [];
        },
        async fetchOrders() {
            this.loading.list = true;
            this.error.list = null;

            try {
                const response = await axios.get("/api/order");
                const data = response.data;

                const orders = Array.isArray(data?.data ?? data) ? data.data ?? data : [];
                this.setOrders(orders);

                return orders;
            } catch (e) {
                console.error("Failed to fetch orders", e);
                this.error.list =
                    e?.response?.data?.message ||
                    "Не удалось загрузить заказы. Попробуйте позже.";
                throw e;
            } finally {
                this.loading.list = false;
            }
        },
        buildCreateOrderPayload(client, selectedAddress) {
            const items = this.cartItems.map((item) => ({
                product_id: item.productId,
                quantity: item.qty,
            }));

            let deliveryAddress = this.deliveryInfo.address || null;

            if (selectedAddress && typeof selectedAddress === "object") {
                const entrance =
                    selectedAddress.entrance ??
                    selectedAddress.entrance_code ??
                    null;

                deliveryAddress = {
                    street: selectedAddress.street ?? deliveryAddress?.street ?? null,
                    house: selectedAddress.house ?? deliveryAddress?.house ?? null,
                    entrance: entrance ?? deliveryAddress?.entrance ?? null,
                    apartment:
                        selectedAddress.apartment ??
                        deliveryAddress?.apartment ??
                        null,
                };
            }

            return {
                client_id: client?.id ?? null,
                items,
                delivery_method: this.deliveryInfo.method,
                delivery_address: deliveryAddress,
                delivery_comment:
                    this.deliveryInfo.comment || this.customerComment || null,
                payment_method: this.paymentInfo.method,
            };
        },
        async createOrder(client, selectedAddress) {
            this.loading.create = true;
            this.error.create = null;

            try {
                const payload = this.buildCreateOrderPayload(
                    client,
                    selectedAddress,
                );
                const response = await axios.post("/api/order", payload);
                const data = response.data;

                const createdOrder = data?.data ?? data ?? null;
                this.lastCreatedOrder = createdOrder;

                if (createdOrder) {
                    this.orders = [createdOrder, ...this.orders];
                }

                this.clearDraft();

                return createdOrder;
            } catch (e) {
                console.error("Failed to create order", e);
                this.error.create =
                    e?.response?.data?.message ||
                    "Не удалось оформить заказ. Попробуйте ещё раз.";
                throw e;
            } finally {
                this.loading.create = false;
            }
        },
    },
});

