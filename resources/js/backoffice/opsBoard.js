import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { backofficeRequest, formatDateTime } from "./api";

/** Kitchen point on the map (Tomsk). */
export const KITCHEN = Object.freeze({
    lat: 56.49771,
    lng: 84.97437,
    label: "Кухня",
});

const STATUS_LABELS = Object.freeze({
    new: "Новый",
    preorder: "Предзаказ",
    confirmed: "Подтверждён",
    preparing: "В производстве",
    ready: "Произведен",
    in_transit: "В пути",
    delivered: "Выполнен",
    cancelled: "Списан",
});

const STATUS_COLORS = Object.freeze({
    new: "#2563eb",
    preorder: "#7c3aed",
    confirmed: "#0891b2",
    preparing: "#d97706",
    ready: "#65a30d",
    in_transit: "#ea580c",
    delivered: "#059669",
    cancelled: "#e11d48",
});

const SOURCE_LABELS = Object.freeze({
    site: "Сайт",
    aggregator: "Агрегатор",
});

const DELIVERY_LABELS = Object.freeze({
    courier: "Курьер",
    pickup: "Самовывоз",
});

const PAYMENT_LABELS = Object.freeze({
    cash: "Наличными",
    card_courier: "Картой курьеру",
    card_online: "Картой онлайн",
    наличные: "Наличными",
    карта: "Картой онлайн",
    "карта курьеру": "Картой курьеру",
});

const PAYMENT_TO_API = Object.freeze({
    наличные: "cash",
    карта: "card_online",
    "карта курьеру": "card_courier",
    cash: "cash",
    card_online: "card_online",
    card_courier: "card_courier",
});

/**
 * @param {string} status
 */
export function statusLabel(status) {
    return STATUS_LABELS[status] ?? (status !== "" ? status : "—");
}

/**
 * @param {string} status
 */
export function statusColor(status) {
    return STATUS_COLORS[status] ?? "#a3a3a3";
}

/**
 * @param {string|null|undefined} source
 */
export function sourceLabel(source) {
    if (source == null || source === "") {
        return "—";
    }
    return SOURCE_LABELS[source] ?? source;
}

/**
 * @param {string|null|undefined} method
 */
export function deliveryMethodLabel(method) {
    if (method == null || method === "") {
        return "—";
    }
    return DELIVERY_LABELS[method] ?? method;
}

/**
 * @param {string|null|undefined} method
 */
export function paymentMethodLabel(method) {
    if (method == null || method === "") {
        return "—";
    }
    return PAYMENT_LABELS[method] ?? method;
}

function mapOrder(row) {
    const client = row.client ?? {};
    const delivery = row.delivery ?? {};
    const payment = row.payment ?? {};
    return {
        id: row.id,
        source: row.source ?? "site",
        status: row.status,
        clientName: client.name ?? "",
        phone: client.phone ?? "",
        email: client.email ?? null,
        clientKind: client.kind ?? "guest",
        clientId: client.client_id ?? null,
        deliveryMethod: delivery.method === "pickup" ? "pickup" : "courier",
        address: delivery.address ?? "",
        scheduledAt: delivery.scheduled_at ?? null,
        persons: delivery.persons ?? null,
        comment: delivery.comment ?? null,
        lat: delivery.latitude ?? null,
        lng: delivery.longitude ?? null,
        payment: payment.method ?? "",
        changeFrom: payment.change_from_rubles ?? null,
        total: row.total ?? 0,
        checkoutId: row.checkout_id ?? null,
        partnerCode: row.partner_code ?? null,
        externalOrderId: row.external_order_id ?? null,
        createdAt: formatDateTime(row.created_at),
        rejectReason: row.reject_reason ?? "",
        lines: Array.isArray(row.lines)
            ? row.lines.map((line) => ({
                name: line.name ?? "",
                qty: line.qty ?? 0,
                total: line.total ?? null,
            }))
            : [],
    };
}

/**
 * Orders from the application API. Transport runs stay in the session:
 * the domain has no run aggregate yet.
 */
export const useOpsBoard = defineStore("ops-board", () => {
    const orders = ref([]);
    const ordersError = ref("");
    const ordersReady = ref(false);
    const journalOrders = ref([]);
    const journalPage = ref(1);
    const journalPerPage = 20;
    const journalTotal = ref(0);
    const journalStatus = ref("");
    const journalReady = ref(false);
    const journalCounts = ref({});
    const nextRunId = ref(1);
    const runs = ref([]);

    const incoming = computed(() =>
        orders.value.filter((order) => order.status === "new" || order.status === "preorder"),
    );

    const inProgress = computed(() =>
        orders.value.filter((order) =>
            ["confirmed", "preparing", "in_transit"].includes(order.status),
        ),
    );

    const kitchenQueue = computed(() =>
        orders.value.filter((order) =>
            order.status === "confirmed" || order.status === "preparing",
        ),
    );

    const pickupQueue = computed(() =>
        orders.value.filter(
            (order) => order.status === "ready" && order.deliveryMethod === "pickup",
        ),
    );

    const draft = computed(() => runs.value.find((run) => run.status === "draft") ?? null);

    const releasedRuns = computed(() => runs.value.filter((run) => run.status === "released"));

    const draftOrderIds = computed(() => {
        const ids = new Set();
        const run = draft.value;
        if (!run) {
            return ids;
        }
        for (const stop of run.stops) {
            if (stop.outcome === "pending") {
                ids.add(stop.orderId);
            }
        }
        return ids;
    });

    const courierPool = computed(() =>
        orders.value.filter(
            (order) =>
                order.status === "ready"
                && order.deliveryMethod === "courier"
                && !draftOrderIds.value.has(order.id),
        ),
    );

    function findOrder(id) {
        return orders.value.find((order) => order.id === id) ?? null;
    }

    function findRun(id) {
        return runs.value.find((run) => run.id === id) ?? null;
    }

    function replaceOrder(row) {
        const next = mapOrder(row);
        const index = orders.value.findIndex((order) => order.id === next.id);
        if (index < 0) {
            orders.value.unshift(next);
            return next;
        }
        orders.value.splice(index, 1, next);
        return next;
    }

    async function loadOrders() {
        try {
            const payload = await backofficeRequest("GET", "/api/backoffice/orders?page=1&per_page=200");
            orders.value = Array.isArray(payload.orders) ? payload.orders.map(mapOrder) : [];
            ordersError.value = "";
        } catch (error) {
            ordersError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
        } finally {
            ordersReady.value = true;
        }
    }

    async function loadJournal(page = journalPage.value, status = journalStatus.value) {
        journalStatus.value = typeof status === "string" ? status : "";
        const params = new URLSearchParams({
            page: String(page),
            per_page: String(journalPerPage),
        });
        if (journalStatus.value !== "") {
            params.set("status", journalStatus.value);
        }
        try {
            const payload = await backofficeRequest("GET", `/api/backoffice/orders?${params}`);
            journalOrders.value = Array.isArray(payload.orders) ? payload.orders.map(mapOrder) : [];
            journalPage.value = Number(payload.page) || 1;
            journalTotal.value = Number(payload.total) || 0;
            journalCounts.value = payload.counts && typeof payload.counts === "object" ? payload.counts : {};
            ordersError.value = "";
        } catch (error) {
            ordersError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
        } finally {
            journalReady.value = true;
        }
    }

    async function changeStatus(id, status, reason) {
        const payload = await backofficeRequest("POST", `/api/backoffice/orders/${id}/status`, {
            status,
            reason: reason ?? null,
        });
        replaceOrder(payload.order);
        ordersError.value = "";
    }

    async function addOrder(input) {
        const clientName = typeof input?.clientName === "string" ? input.clientName.trim() : "";
        const phone = typeof input?.phone === "string" ? input.phone.trim() : "";
        const deliveryMethod = input?.deliveryMethod === "pickup" ? "pickup" : "courier";
        const address = deliveryMethod === "pickup"
            ? ""
            : (typeof input?.address === "string" ? input.address.trim() : "");
        const payment = PAYMENT_TO_API[typeof input?.payment === "string" ? input.payment.trim() : ""] ?? "";
        const lines = Array.isArray(input?.lines)
            ? input.lines
                .map((line) => ({
                    product_id: Number(line?.productId),
                    qty: Number(line?.qty),
                }))
                .filter((line) => Number.isInteger(line.product_id) && line.product_id > 0 && Number.isInteger(line.qty) && line.qty > 0)
            : [];

        if (
            clientName === ""
            || phone === ""
            || (deliveryMethod === "courier" && address === "")
            || payment === ""
            || lines.length === 0
        ) {
            return null;
        }

        try {
            const payload = await backofficeRequest("POST", "/api/backoffice/orders", {
                client_name: clientName,
                phone,
                delivery_method: deliveryMethod,
                address,
                payment_method: payment,
                lines,
            });
            const order = replaceOrder(payload.order);
            ordersError.value = "";
            return order.id;
        } catch (error) {
            ordersError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
            return null;
        }
    }

    async function confirmOrder(id) {
        const order = findOrder(id);
        if (order && order.status !== "new" && order.status !== "preorder") {
            return;
        }
        try {
            await changeStatus(id, "confirmed");
        } catch (error) {
            ordersError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
        }
    }

    async function rejectOrder(id, reason) {
        const text = typeof reason === "string" ? reason.trim() : "";
        if (text === "") {
            return;
        }
        const order = findOrder(id);
        if (order && order.status !== "new" && order.status !== "preorder") {
            return;
        }
        try {
            await changeStatus(id, "cancelled", text);
        } catch (error) {
            ordersError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
        }
    }

    function startPreparing(id) {
        const order = findOrder(id);
        if (!order || order.status !== "confirmed") {
            return;
        }
        order.status = "preparing";
    }

    function markReady(id) {
        const order = findOrder(id);
        if (!order || order.status !== "preparing") {
            return;
        }
        order.status = "ready";
    }

    async function handoffPickup(id) {
        const order = findOrder(id);
        if (order && (order.status !== "ready" || order.deliveryMethod !== "pickup")) {
            return;
        }
        try {
            await changeStatus(id, "delivered");
        } catch (error) {
            ordersError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
        }
    }

    function ensureDraft() {
        const existing = runs.value.find((run) => run.status === "draft");
        if (existing) {
            return existing;
        }
        const run = {
            id: nextRunId.value,
            status: "draft",
            stops: [],
        };
        nextRunId.value += 1;
        runs.value.push(run);
        return run;
    }

    function addToDraft(orderId) {
        const order = findOrder(orderId);
        if (
            !order
            || order.status !== "ready"
            || order.deliveryMethod !== "courier"
            || draftOrderIds.value.has(orderId)
        ) {
            return;
        }
        const run = ensureDraft();
        run.stops.push({ orderId, outcome: "pending", reason: "" });
    }

    function removeFromDraft(orderId) {
        const run = draft.value;
        if (!run) {
            return;
        }
        run.stops = run.stops.filter((stop) => stop.orderId !== orderId);
    }

    function moveStop(orderId, direction) {
        const run = draft.value;
        if (!run) {
            return;
        }
        const index = run.stops.findIndex((stop) => stop.orderId === orderId);
        if (index < 0) {
            return;
        }
        const target = index + direction;
        if (target < 0 || target >= run.stops.length) {
            return;
        }
        const copy = run.stops.slice();
        const [moved] = copy.splice(index, 1);
        copy.splice(target, 0, moved);
        run.stops = copy;
    }

    async function releaseDraft() {
        const run = draft.value;
        if (!run || run.stops.length === 0) {
            return;
        }
        try {
            for (const stop of run.stops) {
                const order = findOrder(stop.orderId);
                if (order && order.status === "ready") {
                    await changeStatus(order.id, "in_transit");
                }
            }
            run.status = "released";
        } catch (error) {
            ordersError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
            await loadOrders();
        }
    }

    function settleStop(runId, orderId, outcome, reason) {
        const run = findRun(runId);
        if (!run || run.status !== "released") {
            return;
        }
        const stop = run.stops.find(
            (entry) => entry.orderId === orderId && entry.outcome === "pending",
        );
        const order = findOrder(orderId);
        if (!stop || !order) {
            return;
        }
        if (outcome === "delivered") {
            stop.outcome = "delivered";
            stop.reason = "";
            order.status = "delivered";
        } else if (outcome === "failed") {
            const text = typeof reason === "string" ? reason.trim() : "";
            if (text === "") {
                return;
            }
            stop.outcome = "failed";
            stop.reason = text;
            order.status = "ready";
        } else {
            return;
        }
        if (run.stops.every((entry) => entry.outcome !== "pending")) {
            run.status = "closed";
        }
    }

    loadOrders();

    return {
        orders,
        ordersError,
        ordersReady,
        journalOrders,
        journalPage,
        journalPerPage,
        journalTotal,
        journalReady,
        journalCounts,
        loadJournal,
        runs,
        incoming,
        inProgress,
        kitchenQueue,
        pickupQueue,
        courierPool,
        draft,
        releasedRuns,
        findOrder,
        addOrder,
        confirmOrder,
        rejectOrder,
        startPreparing,
        markReady,
        handoffPickup,
        addToDraft,
        removeFromDraft,
        moveStop,
        releaseDraft,
        settleStop,
    };
});
