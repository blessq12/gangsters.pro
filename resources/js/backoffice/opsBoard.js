import { defineStore } from "pinia";
import { computed, ref } from "vue";

/** Kitchen point on the mock map (Tomsk). */
export const KITCHEN = Object.freeze({
    lat: 56.49771,
    lng: 84.97437,
    label: "Кухня",
});

const STATUS_LABELS = Object.freeze({
    new: "Новый",
    preorder: "Предзаказ",
    confirmed: "Подтверждён",
    preparing: "Готовится",
    ready: "Готов",
    in_transit: "В пути",
    delivered: "Выдан",
    cancelled: "Отменён",
});

/**
 * @param {string} status
 */
export function statusLabel(status) {
    return STATUS_LABELS[status] ?? status;
}

function createOrder(data) {
    return {
        rejectReason: "",
        ...data,
    };
}

/**
 * In-memory orders and transport runs shared by admin, kitchen and courier screens.
 */
export const useOpsBoard = defineStore("ops-board", () => {
    const orders = ref([
        createOrder({
            id: 101,
            status: "new",
            clientName: "Анна К.",
            phone: "+7 913 000-11-01",
            deliveryMethod: "courier",
            address: "ул. Киевская, 15",
            lat: 56.4812,
            lng: 84.9481,
            payment: "наличные",
            total: 1890,
            lines: [
                { name: "Филадельфия", qty: 1 },
                { name: "Калифорния", qty: 1 },
            ],
        }),
        createOrder({
            id: 102,
            status: "preorder",
            clientName: "Игорь С.",
            phone: "+7 923 000-11-02",
            deliveryMethod: "courier",
            address: "пр. Ленина, 82, к 19:30",
            lat: 56.4754,
            lng: 84.9732,
            payment: "карта курьеру",
            total: 2460,
            lines: [{ name: "Сет Gangster", qty: 1 }],
        }),
        createOrder({
            id: 103,
            status: "new",
            clientName: "Марина П.",
            phone: "+7 952 000-11-03",
            deliveryMethod: "pickup",
            address: "Самовывоз",
            lat: null,
            lng: null,
            payment: "карта",
            total: 980,
            lines: [{ name: "Ролл с угрём", qty: 2 }],
        }),
        createOrder({
            id: 104,
            status: "confirmed",
            clientName: "Олег В.",
            phone: "+7 913 000-11-04",
            deliveryMethod: "courier",
            address: "ул. Нахимова, 8",
            lat: 56.5122,
            lng: 84.991,
            payment: "наличные",
            total: 1540,
            lines: [{ name: "Дракон", qty: 1 }],
        }),
        createOrder({
            id: 105,
            status: "preparing",
            clientName: "Света Л.",
            phone: "+7 923 000-11-05",
            deliveryMethod: "pickup",
            address: "Самовывоз",
            lat: null,
            lng: null,
            payment: "наличные",
            total: 720,
            lines: [{ name: "Мисо", qty: 1 }, { name: "Онигири", qty: 2 }],
        }),
        createOrder({
            id: 106,
            status: "ready",
            clientName: "Павел Д.",
            phone: "+7 952 000-11-06",
            deliveryMethod: "courier",
            address: "ул. Белинского, 21",
            lat: 56.4638,
            lng: 84.9566,
            payment: "карта курьеру",
            total: 2100,
            lines: [{ name: "Сет на двоих", qty: 1 }],
        }),
        createOrder({
            id: 107,
            status: "ready",
            clientName: "Кира Н.",
            phone: "+7 913 000-11-07",
            deliveryMethod: "courier",
            address: "Иркутский тракт, 44",
            lat: 56.5089,
            lng: 85.0412,
            payment: "наличные",
            total: 1320,
            lines: [{ name: "Филадельфия", qty: 2 }],
        }),
        createOrder({
            id: 108,
            status: "ready",
            clientName: "Денис М.",
            phone: "+7 923 000-11-08",
            deliveryMethod: "pickup",
            address: "Самовывоз",
            lat: null,
            lng: null,
            payment: "карта",
            total: 640,
            lines: [{ name: "Калифорния", qty: 1 }],
        }),
        createOrder({
            id: 109,
            status: "in_transit",
            clientName: "Алина Р.",
            phone: "+7 952 000-11-09",
            deliveryMethod: "courier",
            address: "ул. Красноармейская, 120",
            lat: 56.4884,
            lng: 84.9477,
            payment: "наличные",
            total: 1750,
            lines: [{ name: "Сет Gangster", qty: 1 }],
        }),
        createOrder({
            id: 110,
            status: "in_transit",
            clientName: "Тимур А.",
            phone: "+7 913 000-11-10",
            deliveryMethod: "courier",
            address: "ул. Учебная, 50",
            lat: 56.4701,
            lng: 84.9894,
            payment: "карта курьеру",
            total: 990,
            lines: [{ name: "Ролл с угрём", qty: 1 }],
        }),
    ]);

    const nextRunId = ref(13);

    const runs = ref([
        {
            id: 12,
            status: "released",
            stops: [
                { orderId: 109, outcome: "pending", reason: "" },
                { orderId: 110, outcome: "pending", reason: "" },
            ],
        },
    ]);

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

    function confirmOrder(id) {
        const order = findOrder(id);
        if (!order || (order.status !== "new" && order.status !== "preorder")) {
            return;
        }
        order.status = "confirmed";
    }

    function rejectOrder(id, reason) {
        const text = typeof reason === "string" ? reason.trim() : "";
        if (text === "") {
            return;
        }
        const order = findOrder(id);
        if (!order || (order.status !== "new" && order.status !== "preorder")) {
            return;
        }
        order.status = "cancelled";
        order.rejectReason = text;
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

    function handoffPickup(id) {
        const order = findOrder(id);
        if (!order || order.status !== "ready" || order.deliveryMethod !== "pickup") {
            return;
        }
        order.status = "delivered";
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

    function releaseDraft() {
        const run = draft.value;
        if (!run || run.stops.length === 0) {
            return;
        }
        for (const stop of run.stops) {
            const order = findOrder(stop.orderId);
            if (order && order.status === "ready") {
                order.status = "in_transit";
            }
        }
        run.status = "released";
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

    return {
        orders,
        runs,
        incoming,
        inProgress,
        kitchenQueue,
        pickupQueue,
        courierPool,
        draft,
        releasedRuns,
        findOrder,
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
