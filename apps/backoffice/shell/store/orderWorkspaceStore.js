import { defineStore } from "pinia";

function emptyForm() {
    return {
        name: "",
        phone: "",
        clientId: /** @type {number|null} */ (null),
        method: "pickup",
        street: "",
        house: "",
        entrance: "",
        floor: "",
        apartment: "",
        comment: "",
        city: "",
        lat: "",
        lng: "",
        payment_method: "cash",
        scheduled_at: "",
    };
}

function newRequestId() {
    return `admin-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

/**
 * @param {Record<string, unknown>} order
 * @returns {Array<{ key: string, id: number, kind: string, name: string, quantity: number }>}
 */
function cartFromOrder(order) {
    const lines = Array.isArray(order?.cart?.lines) ? order.cart.lines : [];
    return lines
        .filter((line) => {
            const type = String(line?.line_type ?? "regular");
            return type === "regular" || type === "set";
        })
        .map((line) => {
            const id = Number(line.product_id);
            const kind = String(line.line_type) === "set" ? "set" : "product";
            return {
                key: `${kind}:${id}`,
                id,
                kind,
                name: String(line.name ?? `Товар #${id}`),
                quantity: Math.max(1, Number(line.quantity) || 1),
            };
        })
        .filter((line) => Number.isFinite(line.id) && line.id > 0);
}

/**
 * Workspace заказа: create | edit | view (Memento формы + корзина).
 */
export const useOrderWorkspaceStore = defineStore("order-workspace", {
    state: () => ({
        active: false,
        /** @type {'create'|'edit'|'view'} */
        mode: "create",
        orderId: /** @type {number|null} */ (null),
        /** @type {Record<string, unknown>|null} */
        sourceOrder: null,
        form: emptyForm(),
        /** @type {Array<{ key: string, id: number, kind: string, name: string, quantity: number }>} */
        cart: [],
        /** @type {null|number|'declined'} */
        giftDecision: null,
        giftPromptedForEligible: false,
        clientRequestId: newRequestId(),
    }),
    getters: {
        cartCount(state) {
            return state.cart.reduce((sum, line) => sum + Number(line.quantity || 0), 0);
        },
        isReadonly(state) {
            return state.mode === "view";
        },
        cartSignature(state) {
            return state.cart.map((line) => `${line.key}:${line.quantity}`).join("|");
        },
    },
    actions: {
        activateCreate() {
            this.reset();
            this.active = true;
            this.mode = "create";
        },
        /**
         * @param {Record<string, unknown>} order
         * @param {'edit'|'view'} mode
         */
        hydrateFromOrder(order, mode = "edit") {
            this.reset();
            this.active = true;
            this.mode = mode;
            this.sourceOrder = order;
            const id = Number(order?.id);
            this.orderId = Number.isFinite(id) && id > 0 ? id : null;

            const client = order?.client && typeof order.client === "object" ? order.client : {};
            const delivery = order?.delivery && typeof order.delivery === "object" ? order.delivery : {};
            const address = delivery.address && typeof delivery.address === "object" ? delivery.address : {};

            this.form.name = String(client.name ?? "");
            this.form.phone = String(client.phone ?? "");
            this.form.clientId = client.id != null ? Number(client.id) : null;
            this.form.method = delivery.method === "courier" ? "courier" : "pickup";
            this.form.street = String(address.street ?? "");
            this.form.house = String(address.house ?? "");
            this.form.entrance = String(address.entrance ?? "");
            this.form.floor = String(address.floor ?? "");
            this.form.apartment = String(address.apartment ?? "");
            this.form.comment = String(address.comment ?? "");
            this.form.lat = address.lat != null && address.lat !== "" ? String(address.lat) : "";
            this.form.lng = address.lng != null && address.lng !== "" ? String(address.lng) : "";
            this.form.payment_method = String(order.payment_method ?? order.payment?.method ?? "cash");
            const scheduled = order.scheduled_at;
            if (typeof scheduled === "string" && scheduled.trim() !== "") {
                // datetime-local: YYYY-MM-DDTHH:mm
                const d = new Date(scheduled);
                if (!Number.isNaN(d.getTime())) {
                    const pad = (n) => String(n).padStart(2, "0");
                    this.form.scheduled_at = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                } else {
                    this.form.scheduled_at = scheduled.slice(0, 16);
                }
            } else {
                this.form.scheduled_at = "";
            }

            this.cart = cartFromOrder(order);

            const giftLine = (Array.isArray(order?.cart?.lines) ? order.cart.lines : [])
                .find((line) => String(line?.line_type) === "gift");
            this.giftDecision = giftLine?.product_id != null
                ? Number(giftLine.product_id)
                : null;
            this.giftPromptedForEligible = false;
            this.clientRequestId = newRequestId();
        },
        /**
         * Повтор: create с предзаполнением из заказа.
         * @param {Record<string, unknown>} order
         */
        hydrateRepeat(order) {
            this.hydrateFromOrder(order, "edit");
            this.mode = "create";
            this.orderId = null;
            this.sourceOrder = null;
            this.form.scheduled_at = "";
            this.giftDecision = null;
            this.giftPromptedForEligible = false;
            this.clientRequestId = newRequestId();
        },
        /**
         * Снимок заказа в текущий workspace (корзина + delivery + payment).
         * Не меняет mode / orderId / sourceOrder.
         *
         * @param {Record<string, unknown>} order
         * @param {{ keepClient?: boolean }} [options]
         */
        applyOrderSnapshot(order, options = {}) {
            const keepClient = Boolean(options.keepClient);
            const keptClient = keepClient
                ? {
                    name: this.form.name,
                    phone: this.form.phone,
                    clientId: this.form.clientId,
                }
                : null;

            const client = order?.client && typeof order.client === "object" ? order.client : {};
            const delivery = order?.delivery && typeof order.delivery === "object" ? order.delivery : {};
            const address = delivery.address && typeof delivery.address === "object" ? delivery.address : {};

            if (!keepClient) {
                this.form.name = String(client.name ?? "");
                this.form.phone = String(client.phone ?? "");
                this.form.clientId = client.id != null ? Number(client.id) : null;
            }

            this.form.method = delivery.method === "courier" ? "courier" : "pickup";
            this.form.street = String(address.street ?? "");
            this.form.house = String(address.house ?? "");
            this.form.entrance = String(address.entrance ?? "");
            this.form.floor = String(address.floor ?? "");
            this.form.apartment = String(address.apartment ?? "");
            this.form.comment = String(address.comment ?? "");
            this.form.lat = address.lat != null && address.lat !== "" ? String(address.lat) : "";
            this.form.lng = address.lng != null && address.lng !== "" ? String(address.lng) : "";
            this.form.payment_method = String(order.payment_method ?? order.payment?.method ?? "cash");
            this.form.scheduled_at = "";

            this.cart = cartFromOrder(order);
            this.giftDecision = null;
            this.giftPromptedForEligible = false;
            this.clientRequestId = newRequestId();

            if (keptClient) {
                this.form.name = keptClient.name;
                this.form.phone = keptClient.phone;
                this.form.clientId = keptClient.clientId;
            }
        },
        reset() {
            this.active = false;
            this.mode = "create";
            this.orderId = null;
            this.sourceOrder = null;
            this.form = emptyForm();
            this.cart = [];
            this.giftDecision = null;
            this.giftPromptedForEligible = false;
            this.clientRequestId = newRequestId();
        },
        /**
         * @param {{ id: number, kind: string, name: string }} item
         */
        addToCart(item) {
            const key = `${item.kind}:${item.id}`;
            const existing = this.cart.find((line) => line.key === key);
            if (existing) {
                existing.quantity += 1;
                return;
            }
            this.cart.push({
                key,
                id: item.id,
                kind: item.kind,
                name: item.name,
                quantity: 1,
            });
        },
        /**
         * @param {string} key
         * @param {number} delta
         */
        changeQty(key, delta) {
            const line = this.cart.find((item) => item.key === key);
            if (!line) {
                return;
            }
            line.quantity += delta;
            if (line.quantity <= 0) {
                this.cart = this.cart.filter((item) => item.key !== key);
            }
        },
        /**
         * @param {string} key
         */
        removeLine(key) {
            this.cart = this.cart.filter((item) => item.key !== key);
        },
        /**
         * @param {number} branchId
         * @returns {Record<string, unknown>|null}
         */
        buildCheckoutBody(branchId) {
            const id = Number(branchId);
            if (!Number.isFinite(id) || id < 1 || this.cart.length === 0) {
                return null;
            }

            const giftProductId = typeof this.giftDecision === "number" ? this.giftDecision : null;
            const clientId = this.form.clientId != null ? Number(this.form.clientId) : null;

            /** @type {Record<string, unknown>} */
            const body = {
                branch_id: id,
                lines: this.cart.map((line) => ({
                    product_id: line.id,
                    quantity: line.quantity,
                    kind: line.kind === "set" ? "set" : "product",
                })),
                delivery_method: this.form.method,
                client: clientId != null && clientId > 0
                    ? {
                        kind: "client",
                        id: clientId,
                        name: this.form.name.trim() || null,
                        phone: this.form.phone.trim() || null,
                    }
                    : {
                        kind: "guest",
                        name: this.form.name.trim() || null,
                        phone: this.form.phone.trim() || null,
                    },
                payment_method: this.form.payment_method,
                change_from_rubles: null,
                gift_product_id: giftProductId,
                scheduled_at: this.form.scheduled_at.trim() || null,
                address: null,
            };

            if (this.form.method === "courier") {
                body.address = {
                    street: this.form.street.trim(),
                    house: this.form.house.trim(),
                    entrance: this.form.entrance.trim() || null,
                    floor: this.form.floor.trim() || null,
                    apartment: this.form.apartment.trim() || null,
                    comment: this.form.comment.trim() || null,
                    lat: this.form.lat === "" ? null : Number(this.form.lat),
                    lng: this.form.lng === "" ? null : Number(this.form.lng),
                };
            }

            return body;
        },
    },
});
