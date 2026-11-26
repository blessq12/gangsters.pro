import { defineStore } from "pinia";
import { addOrderNote, getOrder } from "../../../api/orders.js";
import { useAuthStore } from "../../store/authStore.js";

/**
 * @param {number|string|null|undefined} orderId
 * @returns {string}
 */
function orderKey(orderId) {
    return String(orderId ?? "");
}

/**
 * Синглтон ленты комментариев к заказу (весь бэкофис).
 */
export const useOrderNotesStore = defineStore("backoffice-order-notes", {
    state: () => ({
        isOpen: false,
        orderId: /** @type {number|string|null} */ (null),
        orderStatus: "",
        notes: /** @type {Array<Record<string, unknown>>} */ ([]),
        draft: "",
        loading: false,
        posting: false,
        error: /** @type {string|null} */ (null),
        /** Инкремент после успешной загрузки/отправки — хост скроллит ленту. */
        scrollToken: 0,
        /**
         * Кэш счётчиков по заказам: переживает close(), чтобы триггеры не обнулялись.
         * @type {Record<string, number>}
         */
        countsByOrderId: {},
    }),
    getters: {
        canCompose: (state) => String(state.orderStatus) !== "delivered",
        noteCount: (state) => state.notes.length,
        /** Хронология как в чате: старые сверху */
        threadNotes: (state) => [...state.notes].reverse(),
        myEmployeeId() {
            const auth = useAuthStore();
            const id = auth.employee?.id;
            return id != null ? Number(id) : null;
        },
    },
    actions: {
        /**
         * Актуальный счётчик для триггера.
         * @param {number|string} orderId
         * @param {number} [fallback=0]
         * @returns {number}
         */
        countFor(orderId, fallback = 0) {
            const key = orderKey(orderId);
            if (!key) {
                return fallback;
            }
            if (this.isOpen && String(this.orderId) === key) {
                return this.notes.length;
            }
            if (Object.prototype.hasOwnProperty.call(this.countsByOrderId, key)) {
                return Number(this.countsByOrderId[key] || 0);
            }
            return fallback;
        },

        /**
         * Запомнить count с экрана (initialNotes / notes_count), не затирая больший fetch.
         * @param {number|string} orderId
         * @param {number} count
         * @param {{ force?: boolean }} [options]
         */
        rememberCount(orderId, count, options = {}) {
            const key = orderKey(orderId);
            if (!key) {
                return;
            }
            const next = Math.max(0, Number(count) || 0);
            const prev = this.countsByOrderId[key];
            if (options.force || prev === undefined || next >= prev) {
                this.countsByOrderId[key] = next;
            }
        },

        /**
         * @param {number|string|null|undefined} orderId
         * @param {number} count
         */
        setCount(orderId, count) {
            const key = orderKey(orderId);
            if (!key) {
                return;
            }
            this.countsByOrderId[key] = Math.max(0, Number(count) || 0);
        },

        /**
         * @param {{
         *   orderId: number|string,
         *   orderStatus?: string,
         *   initialNotes?: unknown[],
         *   notesCount?: number,
         * }} payload
         */
        open(payload) {
            const nextId = payload?.orderId;
            if (nextId == null || nextId === "") {
                return;
            }
            const sameOrder = String(this.orderId) === String(nextId);
            if (!sameOrder) {
                this.draft = "";
                this.error = null;
            }
            this.orderId = nextId;
            this.orderStatus = String(payload.orderStatus ?? this.orderStatus ?? "");
            if (Array.isArray(payload.initialNotes)) {
                this.notes = [...payload.initialNotes];
                this.rememberCount(nextId, payload.initialNotes.length);
            } else if (payload.notesCount != null) {
                this.rememberCount(nextId, payload.notesCount);
                if (!sameOrder) {
                    this.notes = [];
                }
            } else if (!sameOrder) {
                this.notes = [];
            }
            this.isOpen = true;
            void this.loadNotes();
        },

        close() {
            if (this.orderId != null) {
                this.setCount(this.orderId, this.notes.length);
            }
            this.isOpen = false;
            this.draft = "";
            this.error = null;
        },

        /**
         * @param {{ author_employee_id?: number|null }} note
         */
        isMine(note) {
            const mine = this.myEmployeeId;
            if (mine == null || note?.author_employee_id == null) {
                return false;
            }
            return Number(note.author_employee_id) === mine;
        },

        async loadNotes() {
            if (this.orderId == null || this.orderId === "") {
                this.notes = [];
                return;
            }
            const loadingFor = this.orderId;
            this.loading = true;
            this.error = null;
            try {
                const order = await getOrder(loadingFor);
                if (String(this.orderId) !== String(loadingFor)) {
                    return;
                }
                this.notes = Array.isArray(order?.notes) ? order.notes : [];
                this.orderStatus = String(order?.status ?? this.orderStatus ?? "");
                this.setCount(loadingFor, this.notes.length);
                this.scrollToken += 1;
            } catch (err) {
                if (String(this.orderId) !== String(loadingFor)) {
                    return;
                }
                this.notes = [];
                this.error =
                    err instanceof Error ? err.message : "Не удалось загрузить комментарии";
            } finally {
                if (String(this.orderId) === String(loadingFor)) {
                    this.loading = false;
                }
            }
        },

        async submit() {
            const text = String(this.draft || "").trim();
            if (!text || !this.canCompose || this.orderId == null || this.posting) {
                return;
            }
            const forOrder = this.orderId;
            this.posting = true;
            this.error = null;
            try {
                const payload = await addOrderNote(forOrder, { body: text });
                if (String(this.orderId) !== String(forOrder)) {
                    return;
                }
                if (payload?.note) {
                    this.notes = [payload.note, ...this.notes];
                } else {
                    await this.loadNotes();
                }
                this.setCount(forOrder, this.notes.length);
                this.draft = "";
                this.scrollToken += 1;
            } catch (err) {
                this.error = err instanceof Error ? err.message : "Не удалось отправить";
                throw err;
            } finally {
                this.posting = false;
            }
        },
    },
});

/** Facade: закрыть панель. */
export function closeNotes() {
    useOrderNotesStore().close();
}
