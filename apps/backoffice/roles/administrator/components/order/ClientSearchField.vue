<script setup>
import { computed, ref, watch } from "vue";
import { getClient, listClients } from "../../../../api/clients.js";
import { getOrder } from "../../../../api/orders.js";
import { useApi } from "../../../../shell/composables/useApi.js";
import { useToast } from "../../../../shell/composables/useToast.js";
import {
    formatAddress,
    orderStatusLabel,
    orderStatusVariant,
} from "../../../../shell/domain/orderStage.js";
import {
    formatRuPhoneCanonical,
    isRuPhoneComplete,
    normalizeRuPhoneDigits,
} from "../../../../shell/phone/ruPhone.js";
import UiBadge from "../../../../shell/components/ui/UiBadge.vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const SEARCH_MIN_DIGITS = 4;

const props = defineProps({
    name: { type: String, default: "" },
    phone: { type: String, default: "" },
    clientId: { type: [Number, String, null], default: null },
    nameError: { type: String, default: "" },
    phoneError: { type: String, default: "" },
});

const emit = defineEmits([
    "update:name",
    "update:phone",
    "select-client",
    "clear-client",
    "apply-address",
    "apply-order",
]);

const toast = useToast();
const searchApi = useApi();
const detailApi = useApi();
const orderApi = useApi();

const hits = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const open = ref(false);
const searching = ref(false);
/** Цифры последнего поиска. */
const lastSearchedDigits = ref("");
let timer = /** @type {ReturnType<typeof setTimeout>|null} */ (null);

const addresses = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const orders = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const detailLoadedForId = ref(/** @type {number|null} */ (null));

const nameModel = computed({
    get: () => props.name,
    set: (value) => emit("update:name", value),
});

const phoneModel = computed({
    get: () => props.phone,
    set: (value) => emit("update:phone", value),
});

const selected = computed(() => props.clientId != null && props.clientId !== "");

const phoneDigits = computed(() => normalizeRuPhoneDigits(props.phone));

const phoneComplete = computed(() => isRuPhoneComplete(props.phone));

/** Полный номер искали, хитов нет — режим нового клиента. */
const showNewClient = computed(
    () =>
        !selected.value &&
        phoneComplete.value &&
        lastSearchedDigits.value === phoneDigits.value &&
        !searching.value &&
        hits.value.length === 0,
);

watch(
    () => props.phone,
    (value) => {
        if (selected.value) {
            return;
        }
        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(() => runSearch(value), 280);
    },
);

watch(
    () => props.clientId,
    (id) => {
        if (!id) {
            addresses.value = [];
            orders.value = [];
            detailLoadedForId.value = null;
            return;
        }
        open.value = false;
        hits.value = [];
        lastSearchedDigits.value = "";
        const numericId = Number(id);
        if (Number.isFinite(numericId) && numericId > 0 && detailLoadedForId.value !== numericId) {
            loadClientDetail(numericId);
        }
    },
    { immediate: true },
);

watch([phoneComplete, hits, selected], () => {
    if (selected.value) {
        return;
    }
    if (!phoneComplete.value || hits.value.length > 0) {
        if (props.name) {
            emit("update:name", "");
        }
    }
});

/**
 * @param {string|null|undefined} value
 */
async function runSearch(value) {
    const digits = normalizeRuPhoneDigits(value);
    if (digits.length < SEARCH_MIN_DIGITS) {
        hits.value = [];
        open.value = false;
        searching.value = false;
        lastSearchedDigits.value = "";
        return;
    }

    searching.value = true;
    try {
        const payload = await searchApi.run(() => listClients({ q: digits, limit: 8, offset: 0 }));
        const items = Array.isArray(payload?.items) ? payload.items : [];
        hits.value = items;
        open.value = items.length > 0;
        lastSearchedDigits.value = digits;
    } catch {
        hits.value = [];
        open.value = false;
        lastSearchedDigits.value = digits;
    } finally {
        searching.value = false;
    }
}

/**
 * @param {number} id
 */
async function loadClientDetail(id) {
    try {
        const payload = await detailApi.run(() => getClient(id));
        const client = payload?.client ?? null;
        addresses.value = Array.isArray(client?.addresses) ? client.addresses : [];
        orders.value = (Array.isArray(payload?.orders) ? payload.orders : []).map((order) => ({
            id: order.id,
            number: order.number || `ORD-${String(order.id).padStart(4, "0")}`,
            status: order.status,
            total: order.total != null ? `${order.total} ₽` : "—",
            created_at: order.created_at,
            lines: Array.isArray(order.lines) ? order.lines : [],
        }));
        detailLoadedForId.value = id;
    } catch (err) {
        addresses.value = [];
        orders.value = [];
        detailLoadedForId.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить клиента");
    }
}

/**
 * @param {Record<string, unknown>} client
 */
function pick(client) {
    const rawPhone = String(client.phone ?? "");
    const formatted = formatRuPhoneCanonical(rawPhone) || rawPhone;
    emit("select-client", {
        id: Number(client.id),
        name: String(client.name ?? ""),
        phone: formatted,
    });
    hits.value = [];
    open.value = false;
    lastSearchedDigits.value = "";
}

function clearSelected() {
    emit("clear-client");
    hits.value = [];
    open.value = false;
    lastSearchedDigits.value = "";
    addresses.value = [];
    orders.value = [];
    detailLoadedForId.value = null;
}

/**
 * @param {Record<string, unknown>} address
 */
function addressLabel(address) {
    const formatted = formatAddress(address);
    if (formatted) {
        return formatted;
    }
    return String(address?.title || address?.id || "Адрес");
}

/**
 * @param {Record<string, unknown>} address
 */
function onPickAddress(address) {
    emit("apply-address", {
        street: String(address.street ?? ""),
        house: String(address.house ?? ""),
        entrance: String(address.entrance ?? ""),
        floor: String(address.floor ?? ""),
        apartment: String(address.apartment ?? ""),
        comment: String(address.comment ?? ""),
        latitude: address.lat != null && address.lat !== "" ? address.lat : "",
        longitude: address.lng != null && address.lng !== "" ? address.lng : "",
    });
}

/**
 * @param {{ id: number|string }} row
 */
async function onPickOrder(row) {
    if (!row?.id) {
        return;
    }
    try {
        const order = await orderApi.run(() => getOrder(row.id));
        emit("apply-order", order);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказ");
    }
}

function formatOrderDate(value) {
    if (!value) {
        return "—";
    }
    try {
        return new Date(value).toLocaleString("ru-RU");
    } catch {
        return String(value);
    }
}

/**
 * @param {Array<{ name?: string, quantity?: number }>|undefined} lines
 */
function linesPreview(lines) {
    const list = Array.isArray(lines) ? lines : [];
    if (list.length === 0) {
        return "Без позиций";
    }
    return list.map((line) => `${line.name || "Позиция"} × ${line.quantity ?? 1}`).join(", ");
}
</script>

<template>
    <form class="space-y-3" autocomplete="off" @submit.prevent>
        <div
            v-if="selected"
            class="flex flex-wrap items-center justify-between gap-2 border border-bo-border bg-bo-field px-3 py-2 text-sm"
        >
            <div>
                <div class="text-bo-fg">
                    {{ name || "Клиент" }}
                </div>
                <div class="text-xs text-bo-muted">{{ phone || "—" }} · выбранный клиент</div>
            </div>
            <button
                type="button"
                class="text-sm text-bo-muted underline-offset-2 hover:text-bo-fg hover:underline"
                @click="clearSelected"
            >
                Сбросить
            </button>
        </div>

        <template v-else>
            <UiFormField label="Телефон" required :error="phoneError">
                <div class="relative">
                    <UiInput
                        v-model="phoneModel"
                        type="tel"
                        name="bo-client-phone"
                        autocomplete="off"
                        placeholder="Начни вводить номер…"
                    />
                    <div
                        v-if="open"
                        class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-bo-border bg-bo-elevated shadow-bo"
                    >
                        <button
                            v-for="client in hits"
                            :key="String(client.id)"
                            type="button"
                            class="flex w-full flex-col gap-0.5 px-3 py-2.5 text-left text-sm transition-colors hover:bg-white/[0.06]"
                            @click="pick(client)"
                        >
                            <span class="text-bo-fg">{{ client.name || "Без имени" }}</span>
                            <span class="text-xs text-bo-muted">{{ client.phone || "—" }}</span>
                        </button>
                    </div>
                </div>
            </UiFormField>

            <p
                v-if="searching && phoneDigits.length >= SEARCH_MIN_DIGITS"
                class="text-xs text-bo-muted"
            >
                Ищем клиента…
            </p>

            <template v-if="showNewClient">
                <p class="text-xs text-bo-muted">Клиент не найден — оформим заказ на нового.</p>
                <UiFormField label="Имя" required :error="nameError">
                    <UiInput v-model="nameModel" name="bo-client-name" autocomplete="off" />
                </UiFormField>
            </template>
        </template>

        <template v-if="selected">
            <p v-if="detailApi.loading" class="text-xs text-bo-muted">Загрузка истории…</p>

            <div v-else class="space-y-3">
                <div>
                    <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-bo-muted">
                        Адреса
                    </p>
                    <ul v-if="addresses.length" class="max-h-40 space-y-1 overflow-y-auto">
                        <li v-for="address in addresses" :key="String(address.id)">
                            <button
                                type="button"
                                class="w-full border border-bo-border bg-black/20 px-3 py-2 text-left text-sm transition-colors hover:border-bo-accent hover:bg-white/[0.04]"
                                @click="onPickAddress(address)"
                            >
                                <span class="text-bo-fg">{{ addressLabel(address) }}</span>
                                <span
                                    v-if="address.is_default"
                                    class="ml-2 text-xs text-emerald-300"
                                >
                                    основной
                                </span>
                            </button>
                        </li>
                    </ul>
                    <p v-else class="text-xs text-bo-muted">Сохранённых адресов нет</p>
                </div>

                <div>
                    <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-bo-muted">
                        Прошлые заказы
                    </p>
                    <ul v-if="orders.length" class="max-h-48 space-y-1 overflow-y-auto">
                        <li v-for="order in orders" :key="String(order.id)">
                            <button
                                type="button"
                                class="flex w-full flex-col gap-1.5 border border-bo-border bg-black/20 px-3 py-2 text-left text-sm transition-colors hover:border-bo-accent hover:bg-white/[0.04] disabled:opacity-50"
                                :disabled="orderApi.loading"
                                @click="onPickOrder(order)"
                            >
                                <span
                                    class="flex w-full flex-wrap items-center justify-between gap-2"
                                >
                                    <span class="min-w-0">
                                        <span class="text-bo-fg">{{ order.number }}</span>
                                        <span class="ml-2 text-xs text-bo-muted">
                                            {{ formatOrderDate(order.created_at) }}
                                        </span>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-xs text-bo-muted">{{ order.total }}</span>
                                        <UiBadge
                                            :variant="orderStatusVariant(order.status)"
                                            :text="orderStatusLabel(order.status)"
                                        />
                                    </span>
                                </span>
                                <ul
                                    v-if="order.lines?.length"
                                    class="space-y-0.5 text-xs text-bo-muted"
                                >
                                    <li
                                        v-for="(line, idx) in order.lines"
                                        :key="`${order.id}-${idx}`"
                                    >
                                        {{ line.name || "Позиция" }} × {{ line.quantity ?? 1 }}
                                    </li>
                                </ul>
                                <p v-else class="text-xs text-bo-muted">
                                    {{ linesPreview(order.lines) }}
                                </p>
                            </button>
                        </li>
                    </ul>
                    <p v-else class="text-xs text-bo-muted">Заказов пока нет</p>
                    <p class="mt-1 text-[0.7rem] text-bo-muted">
                        Клик по заказу подставит корзину, доставку и оплату
                    </p>
                </div>
            </div>
        </template>
    </form>
</template>
