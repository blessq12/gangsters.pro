<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { ensureClient } from "../../../api/clients.js";
import { getBranch } from "../../../api/company.js";
import {
    acceptOrder,
    cancelOrder,
    completePickup,
    getOrder,
    storeOrder,
    updateOrder,
} from "../../../api/orders.js";
import { getTicket, markTicketLinePacked } from "../../../api/production.js";
import GiftPickModal from "../components/order/GiftPickModal.vue";
import OrderCartPanel from "../components/order/OrderCartPanel.vue";
import OrderClientBlock from "../components/order/OrderClientBlock.vue";
import OrderDeliveryBlock from "../components/order/OrderDeliveryBlock.vue";
import OrderGiftBar from "../components/order/OrderGiftBar.vue";
import OrderPackChecklist from "../components/order/OrderPackChecklist.vue";
import OrderPaymentBlock from "../components/order/OrderPaymentBlock.vue";
import OrderQuoteTotals from "../components/order/OrderQuoteTotals.vue";
import OrderWorkspaceHeader from "../components/order/OrderWorkspaceHeader.vue";
import OrderNotesDropdown from "../../../shell/components/order/OrderNotesDropdown.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useBranchMenu } from "../../../shell/composables/useBranchMenu.js";
import { mapQuoteLinesToUpdate, useOrderQuote } from "../../../shell/composables/useOrderQuote.js";
import { useToast } from "../../../shell/composables/useToast.js";
import {
    administratorCommands,
    branchLabel,
    isClosedOrder,
    orderNumberOf,
    orderStatusLabel,
    orderStatusVariant,
} from "../../../shell/domain/orderStage.js";
import { isRuPhoneComplete } from "../../../shell/phone/ruPhone.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import { useOrderWorkspaceStore } from "../../../shell/store/orderWorkspaceStore.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const workspace = useOrderWorkspaceStore();
const { form, cart, giftDecision, giftPromptedForEligible, mode, orderId, sourceOrder, isReadonly } = storeToRefs(workspace);

const detailApi = useApi();
const actionApi = useApi();
const saveApi = useApi();
const ensureApi = useApi();
const ticketApi = useApi();
const { menuApi, categories, loadMenu } = useBranchMenu();

/** Плоский список меню для дропдауна в корзине. */
const menuItems = computed(() => {
    /** @type {Array<Record<string, unknown>>} */
    const flat = [];
    for (const category of categories.value) {
        for (const item of (category.items || [])) {
            flat.push(item);
        }
    }
    return flat;
});

const branch = ref(/** @type {Record<string, unknown>|null} */ (null));
const ticket = ref(null);
const giftModalOpen = ref(false);
const confirmOpen = ref(false);
const cancelOpen = ref(false);
const pickupOpen = ref(false);
const fieldErrors = reactive(/** @type {Record<string, string>} */ ({}));
let suppressWatchers = false;
const audits = computed(() => {
    const list = sourceOrder.value?.audits;
    return Array.isArray(list) ? list : [];
});

const routeOrderId = computed(() => {
    if (route.name === "administrator-order-new") {
        return null;
    }
    const id = Number(route.params.id);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const isCreateRoute = computed(() => route.name === "administrator-order-new");

const branchId = computed(() => {
    const fromOrder = Number(sourceOrder.value?.branch_id);
    if (Number.isFinite(fromOrder) && fromOrder > 0) {
        return fromOrder;
    }
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const branchCity = computed(() => String(branch.value?.city ?? form.value.city ?? "").trim());

const branchTitle = computed(() => {
    if (branch.value && Number(branch.value.id) === branchId.value) {
        return branchLabel(branch.value);
    }
    return branchId.value ? `Филиал #${branchId.value}` : "Нет филиала";
});

const headerTitle = computed(() => {
    if (mode.value === "create") {
        return "Новый заказ";
    }
    if (sourceOrder.value) {
        return orderNumberOf(sourceOrder.value);
    }
    return orderId.value ? `Заказ #${orderId.value}` : "Заказ";
});

const modeLabel = computed(() => {
    if (mode.value === "create") {
        return "создание";
    }
    if (mode.value === "view") {
        return "просмотр";
    }
    return "редактирование";
});

const primaryLabel = computed(() => {
    if (mode.value === "create") {
        return "Создать заказ";
    }
    if (mode.value === "edit") {
        return "Сохранить";
    }
    return "";
});

const commands = computed(() => {
    if (mode.value === "create" || !sourceOrder.value) {
        return [];
    }
    return administratorCommands(sourceOrder.value);
});

const showPack = computed(() => mode.value === "edit" && sourceOrder.value?.status === "ready");
const showNotesButton = computed(() => mode.value !== "create" && orderId.value != null);
const showRepeat = computed(
    () => mode.value !== "create" && isClosedOrder(sourceOrder.value?.status),
);
const initialNotes = computed(() => {
    const list = sourceOrder.value?.notes;
    return Array.isArray(list) ? list : [];
});

const {
    quoteApi,
    quote,
    freeComplementLines,
    giftLines,
    giftCandidates,
    giftEligible,
    totals,
    quoteInZone,
    refreshQuote,
    scheduleQuote,
    clearQuote,
} = useOrderQuote({
    buildBody: () => workspace.buildCheckoutBody(branchId.value),
    canQuote: () => {
        if (!branchId.value || workspace.cart.length === 0) {
            return false;
        }
        if (form.value.method === "courier") {
            const hasCoords = form.value.lat !== "" && form.value.lng !== "";
            const hasStreetHouse = form.value.street.trim() !== "" && form.value.house.trim() !== "";
            if (!hasCoords || !hasStreetHouse) {
                return false;
            }
        }
        return true;
    },
    onError: (message) => toast.error(message),
});

const addressZoneStatus = computed(() => {
    if (form.value.method !== "courier") {
        return "";
    }
    if (form.value.lat === "" || form.value.lng === "") {
        return "";
    }
    if (!quote.value) {
        return quoteApi.loading ? "Проверяем зону…" : "";
    }
    if (quoteInZone.value === true) {
        const zoneId = quote.value?.delivery?.zone_id;
        return zoneId ? `В зоне доставки · ${zoneId}` : "В зоне доставки";
    }
    if (quoteInZone.value === false) {
        return "Адрес вне зоны доставки";
    }
    return "";
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function validateForm() {
    clearErrors();
    if (workspace.cart.length === 0) {
        fieldErrors.lines = "Добавь позиции из меню";
    }
    if (!isRuPhoneComplete(form.value.phone)) {
        fieldErrors.phone = "Введи полный номер";
    }
    if (!String(form.value.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    if (form.value.method === "courier") {
        if (!String(form.value.street).trim()) {
            fieldErrors.street = "Укажи улицу";
        }
        if (!String(form.value.house).trim()) {
            fieldErrors.house = "Укажи дом";
        }
        if (!fieldErrors.street && !fieldErrors.house && (form.value.lat === "" || form.value.lng === "")) {
            fieldErrors.street = "Выбери адрес из подсказок или на карте";
        }
        if (!fieldErrors.street && !fieldErrors.house && quoteInZone.value === false) {
            fieldErrors.street = "Адрес вне зоны доставки";
        }
    }
    if (!form.value.payment_method) {
        fieldErrors.payment_method = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

/**
 * @param {{ id: number, name: string, phone: string }} client
 */
function onSelectClient(client) {
    form.value.clientId = client.id;
    form.value.name = client.name;
    form.value.phone = client.phone;
    scheduleQuote();
}

function onClearClient() {
    form.value.clientId = null;
    form.value.name = "";
    form.value.phone = "";
    clearQuote();
}

/**
 * @param {Record<string, unknown>} address
 */
function onAddressUpdate(address) {
    if (address.city !== undefined) {
        form.value.city = address.city || "";
    }
    if (address.street !== undefined) {
        form.value.street = address.street || "";
        delete fieldErrors.street;
    }
    if (address.house !== undefined) {
        form.value.house = address.house || "";
        delete fieldErrors.house;
    }
    if (address.entrance !== undefined) {
        form.value.entrance = address.entrance || "";
    }
    if (address.floor !== undefined) {
        form.value.floor = address.floor || "";
    }
    if (address.apartment !== undefined) {
        form.value.apartment = address.apartment || "";
    }
    if (address.comment !== undefined) {
        form.value.comment = address.comment || "";
    }
    if (address.latitude !== undefined) {
        form.value.lat = address.latitude === "" || address.latitude === null
            ? ""
            : String(address.latitude);
        delete fieldErrors.street;
    }
    if (address.longitude !== undefined) {
        form.value.lng = address.longitude === "" || address.longitude === null
            ? ""
            : String(address.longitude);
        delete fieldErrors.street;
    }
    scheduleQuote();
}

async function ensureClientRecord() {
    if (!validateForm() && (fieldErrors.name || fieldErrors.phone)) {
        toast.error("Проверьте клиента");
        return false;
    }
    try {
        const result = await ensureApi.run(() => ensureClient({
            name: form.value.name.trim(),
            phone: form.value.phone.trim(),
        }));
        const client = result?.client;
        if (!client?.id) {
            throw new Error("Клиент не получен");
        }
        form.value.clientId = Number(client.id);
        form.value.name = String(client.name ?? form.value.name);
        form.value.phone = String(client.phone ?? form.value.phone);
        return true;
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сохранить клиента");
        return false;
    }
}

async function loadTicket() {
    if (!orderId.value || sourceOrder.value?.status !== "ready") {
        ticket.value = null;
        return;
    }
    try {
        ticket.value = await ticketApi.run(() => getTicket(orderId.value));
    } catch {
        ticket.value = null;
    }
}

async function loadBranch() {
    if (!branchId.value) {
        branch.value = null;
        return;
    }
    try {
        branch.value = await getBranch(branchId.value);
        const city = String(branch.value?.city ?? "").trim();
        if (city && !String(form.value.city).trim()) {
            form.value.city = city;
        }
    } catch {
        branch.value = null;
    }
}

async function bootstrapCreate() {
    suppressWatchers = true;
    if (!workspace.active || workspace.mode !== "create") {
        // hydrateRepeat уже выставил create — не сбрасываем
        if (!(workspace.active && workspace.mode === "create" && workspace.cart.length > 0)) {
            workspace.activateCreate();
        }
    } else if (workspace.cart.length === 0) {
        workspace.mode = "create";
        workspace.orderId = null;
        workspace.sourceOrder = null;
    }
    clearQuote();
    ticket.value = null;
    await Promise.all([loadBranch(), loadMenu(branchId.value).catch((err) => {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить меню");
    })]);
    queueMicrotask(() => {
        suppressWatchers = false;
        scheduleQuote();
    });
}

async function bootstrapExisting(id) {
    suppressWatchers = true;
    try {
        const order = await detailApi.run(() => getOrder(id));
        const nextMode = order?.status === "delivered" ? "view" : "edit";
        workspace.hydrateFromOrder(order, nextMode);
        clearQuote();
        await Promise.all([
            loadBranch(),
            loadMenu(branchId.value).catch((err) => {
                toast.error(err instanceof Error ? err.message : "Не удалось загрузить меню");
            }),
            loadTicket(),
        ]);
        queueMicrotask(() => {
            suppressWatchers = false;
            scheduleQuote();
        });
    } catch (err) {
        suppressWatchers = false;
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказ");
        router.push({ name: "administrator-live-orders" });
    }
}

async function bootstrap() {
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    if (isCreateRoute.value) {
        await bootstrapCreate();
        return;
    }
    if (!routeOrderId.value) {
        toast.error("Некорректный заказ");
        router.push({ name: "administrator-live-orders" });
        return;
    }
    await bootstrapExisting(routeOrderId.value);
}

async function submitPrimary() {
    if (mode.value === "view") {
        return;
    }
    if (!validateForm()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    const clientOk = await ensureClientRecord();
    if (!clientOk) {
        return;
    }
    if (giftEligible.value && giftDecision.value === null) {
        giftModalOpen.value = true;
        toast.error("Нужно выбрать подарок или отказаться");
        return;
    }

    try {
        const fresh = await refreshQuote();
        if (!fresh) {
            toast.error("Не удалось рассчитать заказ");
            return;
        }

        if (mode.value === "create") {
            const body = {
                ...workspace.buildCheckoutBody(branchId.value),
                client_request_id: workspace.clientRequestId,
            };
            const created = await saveApi.run(() => storeOrder(body));
            toast.success("Заказ создан");
            workspace.reset();
            const id = created?.id ?? created?.order?.id;
            if (id) {
                router.push({ name: "administrator-order", params: { id: String(id) } });
                return;
            }
            router.push({ name: "administrator-live-orders" });
            return;
        }

        const quoteCartLines = Array.isArray(fresh?.cart?.lines) ? fresh.cart.lines : [];
        const lines = mapQuoteLinesToUpdate(quoteCartLines);
        if (lines.length === 0) {
            toast.error("Quote не вернул линии");
            return;
        }

        /** @type {Record<string, unknown>} */
        const payload = {
            client: {
                id: form.value.clientId,
                name: form.value.name.trim(),
                phone: form.value.phone.trim(),
            },
            payment_method: form.value.payment_method,
            change_from_rubles: form.value.payment_method === "cash" && form.value.change_from !== ""
                ? Number(form.value.change_from)
                : null,
            scheduled_at: form.value.scheduled_at.trim() || null,
            lines,
        };

        if (form.value.method === "courier") {
            payload.delivery = {
                method: "courier",
                zone_id: fresh?.delivery?.zone_id ?? sourceOrder.value?.delivery?.zone_id ?? null,
                fee_kopecks: Number(
                    fresh?.totals?.delivery_fee
                    ?? fresh?.delivery?.delivery_fee_kopecks
                    ?? sourceOrder.value?.delivery?.fee_kopecks
                    ?? 0,
                ),
                address: {
                    street: form.value.street.trim(),
                    house: form.value.house.trim(),
                    entrance: form.value.entrance.trim() || null,
                    floor: form.value.floor.trim() || null,
                    apartment: form.value.apartment.trim() || null,
                    comment: form.value.comment.trim() || null,
                    lat: form.value.lat === "" ? null : Number(form.value.lat),
                    lng: form.value.lng === "" ? null : Number(form.value.lng),
                },
            };
        } else {
            payload.delivery = { method: "pickup", fee_kopecks: 0, address: null };
        }

        const updated = await saveApi.run(() => updateOrder(orderId.value, payload));
        const nextMode = updated?.status === "delivered" ? "view" : "edit";
        suppressWatchers = true;
        workspace.hydrateFromOrder(updated, nextMode);
        toast.success("Заказ обновлён");
        await loadTicket();
        queueMicrotask(() => {
            suppressWatchers = false;
            scheduleQuote();
        });
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сохранить");
    }
}

function goBack() {
    if (mode.value === "create") {
        workspace.reset();
    }
    router.push({ name: "administrator-live-orders" });
}

function repeatOrder() {
    if (!sourceOrder.value) {
        return;
    }
    workspace.hydrateRepeat(sourceOrder.value);
    router.push({ name: "administrator-order-new" });
}

function onCommand(id) {
    if (id === "confirm") {
        confirmOpen.value = true;
        return;
    }
    if (id === "pickup") {
        pickupOpen.value = true;
        return;
    }
    if (id === "toRoute") {
        router.push({ name: "administrator-logistics" });
        return;
    }
    if (id === "cancel") {
        cancelOpen.value = true;
    }
}

async function submitConfirm() {
    confirmOpen.value = false;
    try {
        const updated = await actionApi.run(() => acceptOrder(orderId.value));
        workspace.hydrateFromOrder(updated, updated?.status === "delivered" ? "view" : "edit");
        toast.success("Заказ подтверждён");
        await loadTicket();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось подтвердить");
    }
}

async function submitCancel() {
    cancelOpen.value = false;
    try {
        const updated = await actionApi.run(() => cancelOrder(orderId.value));
        workspace.hydrateFromOrder(updated, "view");
        toast.success("Заказ отменён");
        ticket.value = null;
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отменить");
    }
}

async function submitPickup() {
    pickupOpen.value = false;
    try {
        const updated = await actionApi.run(() => completePickup(orderId.value));
        workspace.hydrateFromOrder(updated, "view");
        toast.success("Заказ выдан");
        ticket.value = null;
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось выдать");
    }
}

async function onPackLine(line) {
    if (!line?.id || !orderId.value) {
        return;
    }
    try {
        ticket.value = await actionApi.run(() => markTicketLinePacked(orderId.value, line.id));
        const updated = await detailApi.run(() => getOrder(orderId.value));
        workspace.hydrateFromOrder(updated, updated?.status === "delivered" ? "view" : "edit");
        toast.success("Позиция собрана");
        if (updated?.status !== "ready") {
            ticket.value = null;
        }
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отметить сборку");
    }
}

function onGiftPick(productId) {
    giftDecision.value = Number(productId);
    giftModalOpen.value = false;
    scheduleQuote();
}

function onGiftDecline() {
    giftDecision.value = "declined";
    giftModalOpen.value = false;
    scheduleQuote();
}

function onAddToCart(item) {
    if (isReadonly.value) {
        return;
    }
    workspace.addToCart(item);
}

function onChangeQty(key, delta) {
    if (isReadonly.value) {
        return;
    }
    workspace.changeQty(key, delta);
}

function onRemoveLine(key) {
    if (isReadonly.value) {
        return;
    }
    workspace.removeLine(key);
}

watch(
    () => [route.name, route.params.id],
    () => {
        bootstrap();
    },
);

watch(
    () => workspace.cartSignature,
    () => {
        if (suppressWatchers || isReadonly.value) {
            return;
        }
        giftDecision.value = null;
        giftPromptedForEligible.value = false;
        scheduleQuote();
    },
);

watch(
    () => [
        form.value.method,
        form.value.payment_method,
        form.value.change_from,
        form.value.scheduled_at,
        form.value.clientId,
    ],
    () => {
        if (!suppressWatchers && !isReadonly.value) {
            scheduleQuote();
        }
    },
);

watch(giftDecision, () => {
    if (!suppressWatchers && !isReadonly.value) {
        scheduleQuote();
    }
});

watch(giftEligible, (eligible) => {
    if (suppressWatchers || isReadonly.value) {
        return;
    }
    if (eligible && giftDecision.value === null && !giftPromptedForEligible.value) {
        giftPromptedForEligible.value = true;
        giftModalOpen.value = true;
    }
    if (!eligible && typeof giftDecision.value === "number") {
        giftDecision.value = null;
    }
});

onMounted(bootstrap);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <OrderWorkspaceHeader
            :title="headerTitle"
            :status-label="sourceOrder ? orderStatusLabel(sourceOrder.status) : ''"
            :status-variant="sourceOrder ? orderStatusVariant(sourceOrder.status) : 'neutral'"
            :mode-label="modeLabel"
            back-label="К списку"
            :primary-label="primaryLabel"
            :primary-disabled="saveApi.loading || quoteApi.loading || ensureApi.loading"
            :secondary-label="showRepeat ? 'Повторить' : ''"
            :commands="commands"
            :commands-disabled="actionApi.loading"
            @back="goBack"
            @primary="submitPrimary"
            @secondary="repeatOrder"
            @command="onCommand"
        >
            <template
                v-if="showPack || showNotesButton"
                #actions
            >
                <OrderPackChecklist
                    v-if="showPack"
                    :lines="ticket?.lines ?? []"
                    :loading="ticketApi.loading"
                    :action-loading="actionApi.loading"
                    :active="showPack"
                    @pack="onPackLine"
                />
                <OrderNotesDropdown
                    v-if="showNotesButton"
                    :order-id="orderId"
                    :initial-notes="initialNotes"
                />
            </template>
        </OrderWorkspaceHeader>

        <p
            v-if="detailApi.loading && mode !== 'create' && !sourceOrder"
            class="text-sm text-bo-muted"
        >
            Загрузка…
        </p>

        <template v-else>
            <p class="text-sm text-bo-muted">
                {{ branchTitle }}
            </p>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-1">
                    <OrderClientBlock
                        :name="form.name"
                        :phone="form.phone"
                        :client-id="form.clientId"
                        :name-error="fieldErrors.name || ''"
                        :phone-error="fieldErrors.phone || ''"
                        :readonly="isReadonly"
                        @update:name="form.name = $event"
                        @update:phone="form.phone = $event"
                        @select-client="onSelectClient"
                        @clear-client="onClearClient"
                    />
                    <OrderDeliveryBlock
                        :method="form.method"
                        :city="form.city"
                        :street="form.street"
                        :house="form.house"
                        :entrance="form.entrance"
                        :floor="form.floor"
                        :apartment="form.apartment"
                        :comment="form.comment"
                        :latitude="form.lat"
                        :longitude="form.lng"
                        :branch-city="branchCity"
                        :street-error="fieldErrors.street || ''"
                        :house-error="fieldErrors.house || ''"
                        :zone-status="addressZoneStatus"
                        :readonly="isReadonly"
                        @update:method="form.method = $event; scheduleQuote()"
                        @address-update="onAddressUpdate"
                    />
                    <OrderPaymentBlock
                        :payment-method="form.payment_method"
                        :change-from="form.change_from"
                        :scheduled-at="form.scheduled_at"
                        :payment-error="fieldErrors.payment_method || ''"
                        :readonly="isReadonly"
                        @update:payment-method="form.payment_method = $event"
                        @update:change-from="form.change_from = $event"
                        @update:scheduled-at="form.scheduled_at = $event"
                    />
                    <UiCard
                        v-if="audits.length"
                        title="Аудит"
                    >
                        <ul class="space-y-2 text-xs text-bo-muted">
                            <li
                                v-for="audit in audits"
                                :key="audit.id"
                            >
                                {{ audit.created_at }}
                                · правки: {{ (audit.changes ?? []).map((c) => c.field).join(", ") }}
                            </li>
                        </ul>
                    </UiCard>
                </div>

                <div class="space-y-4 lg:col-span-2">
                    <UiCard title="Корзина">
                        <p
                            v-if="fieldErrors.lines"
                            class="mb-2 text-xs text-red-300"
                        >
                            {{ fieldErrors.lines }}
                        </p>
                        <OrderCartPanel
                            :cart="cart"
                            :free-complement-lines="freeComplementLines"
                            :gift-lines="giftLines"
                            :menu-items="menuItems"
                            :menu-loading="menuApi.loading"
                            :readonly="isReadonly"
                            @add="onAddToCart"
                            @change-qty="onChangeQty"
                            @remove="onRemoveLine"
                        />
                        <OrderGiftBar
                            class="mt-3"
                            :gift-eligible="giftEligible"
                            :gift-decision="giftDecision"
                            :readonly="isReadonly"
                            @open="giftModalOpen = true"
                        />
                        <OrderQuoteTotals
                            class="mt-3"
                            :cart-count="workspace.cartCount"
                            :items-rubles="totals.items"
                            :delivery-rubles="totals.delivery"
                            :grand-rubles="totals.grand"
                            :loading="quoteApi.loading"
                            :show-delivery="form.method === 'courier'"
                        />
                    </UiCard>
                </div>
            </div>
        </template>

        <GiftPickModal
            :open="giftModalOpen"
            :candidates="giftCandidates"
            @close="giftModalOpen = false"
            @pick="onGiftPick"
            @decline="onGiftDecline"
        />

        <UiConfirmDialog
            :open="confirmOpen"
            title="Подтвердить заказ?"
            message="Филиал уже на заказе. Появятся тикет в очереди и платёж в ожидании."
            @confirm="submitConfirm"
            @cancel="confirmOpen = false"
        />
        <UiConfirmDialog
            :open="pickupOpen"
            title="Выдать самовывоз?"
            message="Заказ станет выданным, платёж — оплаченным."
            @confirm="submitPickup"
            @cancel="pickupOpen = false"
        />
        <UiConfirmDialog
            :open="cancelOpen"
            title="Отменить заказ?"
            message="Отмена закроет тикет, остановку маршрутного листа и платёж."
            @confirm="submitCancel"
            @cancel="cancelOpen = false"
        />
    </div>
</template>
