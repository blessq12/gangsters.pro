<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { getCatalog } from "../../../api/catalog.js";
import { ensureClient } from "../../../api/clients.js";
import {
    getBranch,
    getBranchMenu,
    listBranchCategories,
    listBranchSets,
} from "../../../api/company.js";
import { quoteOrder, storeOrder } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { branchLabel } from "../../../shell/domain/orderStage.js";
import { isRuPhoneComplete } from "../../../shell/phone/ruPhone.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import ClientSearchField from "../components/order/ClientSearchField.vue";
import DeliveryAddressFields from "../components/order/DeliveryAddressFields.vue";
import DeliveryAddressMapPicker from "../components/order/DeliveryAddressMapPicker.vue";
import GiftPickModal from "../components/order/GiftPickModal.vue";
import PosMenuPanel from "../components/order/PosMenuPanel.vue";

const STEPS = [
    { id: "client", label: "Клиент" },
    { id: "menu", label: "Меню" },
    { id: "fulfillment", label: "Доставка" },
    { id: "confirm", label: "Подтверждение" },
];

const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);

const menuApi = useApi();
const quoteApi = useApi();
const saveApi = useApi();
const ensureApi = useApi();

const stepIndex = ref(0);
const stepId = computed(() => STEPS[stepIndex.value]?.id ?? "client");

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const branch = ref(/** @type {Record<string, unknown>|null} */ (null));

const branchTitle = computed(() => {
    if (!branchId.value) {
        return "Нет филиала смены";
    }
    if (branch.value && Number(branch.value.id) === branchId.value) {
        return branchLabel(branch.value);
    }
    return `Филиал #${branchId.value}`;
});

const branchCity = computed(() => String(branch.value?.city ?? form.city ?? "").trim());

/** Карта — опциональный способ указать адрес. */
const mapOpen = ref(false);

const categories = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const cart = ref(/** @type {Array<{ key: string, id: number, kind: string, name: string, quantity: number }>} */ ([]));
const quote = ref(/** @type {Record<string, unknown>|null} */ (null));
const fieldErrors = reactive({});
const clientRequestId = ref(`admin-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`);

/** null | number | 'declined' */
const giftDecision = ref(/** @type {null|number|'declined'} */ (null));
const giftModalOpen = ref(false);
const giftPromptedForEligible = ref(false);

const cartCount = computed(() =>
    cart.value.reduce((sum, line) => sum + Number(line.quantity || 0), 0),
);

const form = reactive({
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
    change_from: "",
});

let quoteTimer = /** @type {ReturnType<typeof setTimeout>|null} */ (null);

const giftCandidates = computed(() => {
    const benefits = quote.value?.benefits;
    if (!benefits || typeof benefits !== "object") {
        return [];
    }
    return Array.isArray(benefits.gift_candidates) ? benefits.gift_candidates : [];
});

const giftEligible = computed(() => Boolean(quote.value?.benefits?.gift_eligible));

const quoteLines = computed(() => {
    const lines = quote.value?.cart?.lines;
    return Array.isArray(lines) ? lines : [];
});

/**
 * @param {Record<string, unknown>|null|undefined} line
 */
function quoteLineKind(line) {
    return String(line?.payload?.kind ?? "regular");
}

/** Бесплатные комплектные линии из quote — только показ в корзине. */
const freeComplementLines = computed(() =>
    quoteLines.value.filter((line) => quoteLineKind(line) === "complement"),
);

/** Основные линии (товар + набор) для шага подтверждения. */
const confirmMainLines = computed(() =>
    quoteLines.value.filter((line) => {
        const kind = quoteLineKind(line);
        return kind === "regular" || kind === "set";
    }),
);

/** Комплектные линии для шага подтверждения. */
const confirmComplementLines = computed(() =>
    quoteLines.value.filter((line) => quoteLineKind(line) === "complement"),
);

/** Линии подарка для шага подтверждения. */
const confirmGiftLines = computed(() =>
    quoteLines.value.filter((line) => quoteLineKind(line) === "gift"),
);

const totals = computed(() => {
    const source = quote.value?.totals || quote.value?.cart?.totals || {};
    return {
        items: Number(source.items_total_rubles ?? source.items_rubles ?? 0),
        delivery: Number(source.delivery_fee_rubles ?? 0),
        grand: Number(source.grand_total_rubles ?? 0),
    };
});

const deliveryFeeLabel = computed(() => {
    const delivery = quote.value?.delivery;
    if (!delivery || form.method !== "courier") {
        return null;
    }
    const fee = Number(delivery.delivery_fee_rubles ?? delivery.fee ?? totals.value.delivery);
    if (quote.value?.benefits?.delivery_reached) {
        return "Бесплатная доставка";
    }
    return `Доставка: ${fee} ₽`;
});

const quoteInZone = computed(() => {
    const raw = quote.value?.delivery?.in_zone;
    if (raw === true) {
        return true;
    }
    if (raw === false) {
        return false;
    }
    return null;
});

/** Статус зоны после выбора адреса (coords → quote → matchZone). */
const addressZoneStatus = computed(() => {
    if (form.method !== "courier") {
        return "";
    }
    if (form.lat === "" || form.lng === "") {
        return "";
    }
    if (!quote.value) {
        return quoteApi.loading ? "Проверяем зону…" : "";
    }
    if (quoteInZone.value === true) {
        const zoneId = quote.value?.delivery?.zone_id;
        return zoneId
            ? `В зоне доставки · ${zoneId}`
            : "В зоне доставки";
    }
    if (quoteInZone.value === false) {
        return "Адрес вне зоны доставки";
    }
    return "";
});

const paymentLabel = computed(() => {
    if (form.payment_method === "card_courier") {
        return "Картой курьеру";
    }
    return "Наличные";
});

const methodLabel = computed(() => (form.method === "courier" ? "Курьер" : "Самовывоз"));

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function rubles(value) {
    return `${Number(value || 0)} ₽`;
}

/**
 * @param {{ id: number, kind: string, name: string }} item
 */
function addToCart(item) {
    const key = `${item.kind}:${item.id}`;
    const existing = cart.value.find((line) => line.key === key);
    if (existing) {
        existing.quantity += 1;
        return;
    }
    cart.value.push({
        key,
        id: item.id,
        kind: item.kind,
        name: item.name,
        quantity: 1,
    });
}

/**
 * @param {string} key
 * @param {number} delta
 */
function changeQty(key, delta) {
    const line = cart.value.find((item) => item.key === key);
    if (!line) {
        return;
    }
    line.quantity += delta;
    if (line.quantity <= 0) {
        cart.value = cart.value.filter((item) => item.key !== key);
    }
}

function removeLine(key) {
    cart.value = cart.value.filter((item) => item.key !== key);
}

/**
 * @param {{ id: number, name: string, phone: string }} client
 */
function onSelectClient(client) {
    form.clientId = client.id;
    form.name = client.name;
    form.phone = client.phone;
}

function onClearClient() {
    form.clientId = null;
    form.name = "";
    form.phone = "";
}

/**
 * @param {{
 *   city?: string,
 *   street?: string,
 *   house?: string,
 *   entrance?: string,
 *   floor?: string,
 *   apartment?: string,
 *   comment?: string,
 *   latitude?: number|string|null,
 *   longitude?: number|string|null,
 * }} address
 */
function onAddressUpdate(address) {
    if (address.city !== undefined) {
        form.city = address.city || "";
    }
    if (address.street !== undefined) {
        form.street = address.street || "";
        delete fieldErrors.street;
    }
    if (address.house !== undefined) {
        form.house = address.house || "";
        delete fieldErrors.house;
    }
    if (address.entrance !== undefined) {
        form.entrance = address.entrance || "";
    }
    if (address.floor !== undefined) {
        form.floor = address.floor || "";
    }
    if (address.apartment !== undefined) {
        form.apartment = address.apartment || "";
    }
    if (address.comment !== undefined) {
        form.comment = address.comment || "";
    }
    if (address.latitude !== undefined) {
        form.lat = address.latitude === "" || address.latitude === null
            ? ""
            : String(address.latitude);
        delete fieldErrors.street;
    }
    if (address.longitude !== undefined) {
        form.lng = address.longitude === "" || address.longitude === null
            ? ""
            : String(address.longitude);
        delete fieldErrors.street;
    }
}

function buildCheckoutBody() {
    const lines = cart.value.map((line) => ({
        product_id: line.id,
        quantity: line.quantity,
        kind: line.kind === "set" ? "set" : "product",
    }));

    const giftProductId = typeof giftDecision.value === "number" ? giftDecision.value : null;

    /** @type {Record<string, unknown>} */
    const body = {
        branch_id: branchId.value,
        lines,
        delivery_method: form.method,
        client: {
            kind: "client",
            id: form.clientId,
            name: form.name.trim() || null,
            phone: form.phone.trim() || null,
        },
        payment_method: form.payment_method,
        change_from_rubles: form.payment_method === "cash" && form.change_from !== ""
            ? Number(form.change_from)
            : null,
        gift_product_id: giftProductId,
        address: null,
    };

    if (form.method === "courier") {
        body.address = {
            street: form.street.trim(),
            house: form.house.trim(),
            entrance: form.entrance.trim() || null,
            floor: form.floor.trim() || null,
            apartment: form.apartment.trim() || null,
            comment: form.comment.trim() || null,
            lat: form.lat === "" ? null : Number(form.lat),
            lng: form.lng === "" ? null : Number(form.lng),
        };
    }

    return body;
}

async function refreshQuote() {
    if (!branchId.value || cart.value.length === 0 || !form.clientId) {
        quote.value = null;
        return;
    }
    if (form.method === "courier") {
        const hasCoords = form.lat !== "" && form.lng !== "";
        const hasStreetHouse =
            String(form.street).trim() !== "" && String(form.house).trim() !== "";
        if (!hasCoords || !hasStreetHouse) {
            quote.value = null;
            return;
        }
    }

    const body = buildCheckoutBody();
    try {
        quote.value = await quoteApi.run(() => quoteOrder(body));
    } catch (err) {
        quote.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось рассчитать заказ");
    }
}

function scheduleQuote() {
    if (quoteTimer) {
        clearTimeout(quoteTimer);
    }
    quoteTimer = setTimeout(() => {
        refreshQuote();
    }, 320);
}

function validateClientStep() {
    clearErrors();
    if (!isRuPhoneComplete(form.phone)) {
        fieldErrors.phone = "Введи полный номер";
    }
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

function validateMenuStep() {
    clearErrors();
    if (cart.value.length === 0) {
        fieldErrors.lines = "Добавь позиции из меню";
    }
    return Object.keys(fieldErrors).length === 0;
}

function validateFulfillmentStep() {
    clearErrors();
    if (form.method === "courier") {
        if (!String(form.street).trim()) {
            fieldErrors.street = "Укажи улицу";
        }
        if (!String(form.house).trim()) {
            fieldErrors.house = "Укажи дом";
        }
        if (!fieldErrors.street && !fieldErrors.house && (form.lat === "" || form.lng === "")) {
            fieldErrors.street = "Выбери адрес из подсказок или на карте";
        }
        if (!fieldErrors.street && !fieldErrors.house && quoteInZone.value === false) {
            fieldErrors.street = "Адрес вне зоны доставки";
        }
    }
    if (!form.payment_method) {
        fieldErrors.payment_method = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function goNext() {
    if (stepId.value === "client") {
        if (!validateClientStep()) {
            toast.error("Проверьте клиента");
            return;
        }
        try {
            const result = await ensureApi.run(() => ensureClient({
                name: form.name.trim(),
                phone: form.phone.trim(),
            }));
            const client = result?.client;
            if (!client?.id) {
                throw new Error("Клиент не получен");
            }
            form.clientId = Number(client.id);
            form.name = String(client.name ?? form.name);
            form.phone = String(client.phone ?? form.phone);
            if (result.created) {
                toast.success("Клиент создан в CRM");
            }
            stepIndex.value += 1;
        } catch (err) {
            toast.error(err instanceof Error ? err.message : "Не удалось сохранить клиента");
        }
        return;
    }

    if (stepId.value === "menu") {
        if (!validateMenuStep()) {
            toast.error("Корзина пуста");
            return;
        }
        stepIndex.value += 1;
        scheduleQuote();
        return;
    }

    if (stepId.value === "fulfillment") {
        if (form.method === "courier" && form.lat !== "" && form.lng !== "") {
            await refreshQuote();
        }
        if (!validateFulfillmentStep()) {
            toast.error("Проверьте доставку и оплату");
            return;
        }
        stepIndex.value += 1;
        await refreshQuote();
        if (giftEligible.value && giftDecision.value === null && !giftPromptedForEligible.value) {
            giftPromptedForEligible.value = true;
            giftModalOpen.value = true;
        }
        return;
    }
}

function goBack() {
    if (stepIndex.value <= 0) {
        return;
    }
    stepIndex.value -= 1;
}

async function createOrder() {
    if (!form.clientId) {
        toast.error("Сначала сохрани клиента");
        stepIndex.value = 0;
        return;
    }
    if (!validateMenuStep() || !validateFulfillmentStep()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    if (giftEligible.value && giftDecision.value === null) {
        giftModalOpen.value = true;
        toast.error("Нужно выбрать подарок или отказаться");
        return;
    }

    const body = {
        ...buildCheckoutBody(),
        client_request_id: clientRequestId.value,
    };

    try {
        const created = await saveApi.run(() => storeOrder(body));
        toast.success("Заказ создан");
        const id = created?.id ?? created?.order?.id;
        if (id) {
            router.push({ name: "administrator-order", params: { id: String(id) } });
            return;
        }
        router.push({ name: "administrator-live-orders" });
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка создания");
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

function openGiftModal() {
    giftModalOpen.value = true;
}

async function loadBranch() {
    if (!branchId.value) {
        branch.value = null;
        return;
    }
    try {
        branch.value = await getBranch(branchId.value);
        const city = String(branch.value?.city ?? "").trim();
        if (city && !String(form.city).trim()) {
            form.city = city;
        }
    } catch {
        branch.value = null;
    }
}

async function loadMenu() {
    if (!branchId.value) {
        categories.value = [];
        return;
    }
    try {
        const [catalog, menu, cats, sets] = await menuApi.run(() => Promise.all([
            getCatalog(),
            getBranchMenu(branchId.value),
            listBranchCategories(branchId.value),
            listBranchSets(branchId.value),
        ]));

        const products = Array.isArray(catalog?.products) ? catalog.products : [];
        const productById = new Map(products.map((item) => [Number(item.id), item]));

        const menuById = new Map(
            (Array.isArray(menu?.items) ? menu.items : [])
                .map((item) => [Number(item.catalog_item_id), item]),
        );

        const setById = new Map(
            (Array.isArray(sets?.items) ? sets.items : Array.isArray(sets) ? sets : [])
                .map((item) => [Number(item.id), item]),
        );

        const categoryRows = Array.isArray(cats?.items) ? cats.items : [];

        /** @type {Array<Record<string, unknown>>} */
        const built = [];

        for (const category of categoryRows) {
            if (category?.is_active === false) {
                continue;
            }
            /** @type {Array<Record<string, unknown>>} */
            const items = [];
            for (const entry of (category.items || [])) {
                const entryId = Number(entry.entry_id);
                const kind = entry.entry_kind === "set" ? "set" : "product";
                if (kind === "set") {
                    const set = setById.get(entryId);
                    if (!set || set.is_available === false) {
                        continue;
                    }
                    items.push({
                        key: `set:${entryId}`,
                        id: entryId,
                        kind: "set",
                        name: String(set.name || `Набор #${entryId}`),
                        price_rubles: Number(set.price_rubles ?? Math.trunc(Number(set.price_kopecks || 0) / 100)),
                    });
                    continue;
                }
                const menuItem = menuById.get(entryId);
                if (!menuItem || menuItem.is_available === false) {
                    continue;
                }
                const product = productById.get(entryId);
                items.push({
                    key: `product:${entryId}`,
                    id: entryId,
                    kind: "product",
                    name: String(product?.name || `Товар #${entryId}`),
                    price_rubles: Number(menuItem.price_rubles ?? Math.trunc(Number(menuItem.price_kopecks || 0) / 100)),
                });
            }
            if (items.length === 0) {
                continue;
            }
            built.push({
                id: String(category.id),
                label: String(category.name || "Категория"),
                items,
            });
        }

        if (built.length === 0) {
            const flat = [];
            for (const [id, menuItem] of menuById.entries()) {
                if (menuItem.is_available === false) {
                    continue;
                }
                const product = productById.get(id);
                flat.push({
                    key: `product:${id}`,
                    id,
                    kind: "product",
                    name: String(product?.name || `Товар #${id}`),
                    price_rubles: Number(menuItem.price_rubles ?? Math.trunc(Number(menuItem.price_kopecks || 0) / 100)),
                });
            }
            if (flat.length > 0) {
                built.push({ id: "all", label: "Меню", items: flat });
            }
        }

        categories.value = built;
    } catch (err) {
        categories.value = [];
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить меню");
    }
}

watch(
    () => [
        cart.value.map((line) => `${line.key}:${line.quantity}`).join("|"),
        form.method,
        form.payment_method,
        form.street,
        form.house,
        form.lat,
        form.lng,
        form.clientId,
        giftDecision.value,
        stepId.value,
    ],
    () => {
        if (stepId.value === "menu" || stepId.value === "fulfillment" || stepId.value === "confirm") {
            scheduleQuote();
        }
    },
    { deep: false },
);

watch(giftEligible, (eligible) => {
    if (!eligible) {
        giftDecision.value = null;
        giftPromptedForEligible.value = false;
    }
});

watch(
    () => form.payment_method,
    (method) => {
        if (method !== "cash") {
            form.change_from = "";
        }
    },
);

watch(
    () => form.method,
    (method) => {
        if (method !== "courier") {
            form.street = "";
            form.house = "";
            form.entrance = "";
            form.floor = "";
            form.apartment = "";
            form.comment = "";
            form.lat = "";
            form.lng = "";
            form.city = "";
        }
    },
);

watch(
    () => [form.phone, form.name],
    () => {
        // Смена телефона/имени на шаге клиента сбрасывает привязку — ensure заново.
        if (stepId.value === "client" && form.clientId) {
            // оставляем id до Next; ensure идемпотентен
        }
    },
);

onMounted(async () => {
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    await Promise.all([loadBranch(), loadMenu()]);
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">
                    Новый заказ
                </h1>
                <p class="mt-1 text-sm text-bo-muted">
                    {{ branchTitle }}
                </p>
            </div>
            <UiButton
                class="!bg-white/10 !text-bo-fg"
                @click="router.push({ name: 'administrator-live-orders' })"
            >
                К списку
            </UiButton>
        </div>

        <p
            v-if="!branchId"
            class="border border-bo-border bg-bo-elevated px-4 py-6 text-sm text-bo-muted"
        >
            У сотрудника не указан филиал смены — создать заказ нельзя.
        </p>

        <template v-else>
            <nav class="flex flex-wrap gap-2">
                <div
                    v-for="(step, index) in STEPS"
                    :key="step.id"
                    class="flex items-center gap-2"
                >
                    <span
                        class="inline-flex h-8 items-center px-3 text-xs font-medium uppercase tracking-wide"
                        :class="index === stepIndex
                            ? 'bg-bo-accent text-white'
                            : index < stepIndex
                                ? 'bg-white/10 text-bo-fg'
                                : 'bg-white/[0.04] text-bo-muted'"
                    >
                        {{ index + 1 }}. {{ step.label }}
                    </span>
                    <span
                        v-if="index < STEPS.length - 1"
                        class="hidden text-bo-muted sm:inline"
                    >→</span>
                </div>
            </nav>

            <!-- Шаг 1: Клиент -->
            <UiCard
                v-if="stepId === 'client'"
                title="Клиент"
            >
                <ClientSearchField
                    v-model:name="form.name"
                    v-model:phone="form.phone"
                    :client-id="form.clientId"
                    :name-error="fieldErrors.name"
                    :phone-error="fieldErrors.phone"
                    @select-client="onSelectClient"
                    @clear-client="onClearClient"
                />
                <p class="mt-3 text-xs text-bo-muted">
                    При «Далее» клиент найдётся или создастся в CRM по телефону.
                </p>
            </UiCard>

            <!-- Шаг 2: Меню + корзина -->
            <div
                v-else-if="stepId === 'menu'"
                class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(260px,320px)]"
            >
                <UiCard title="Меню филиала">
                    <div class="min-h-[50vh]">
                        <PosMenuPanel
                            :categories="categories"
                            :loading="menuApi.loading"
                            @add="addToCart"
                        />
                    </div>
                </UiCard>

                <aside class="space-y-3 lg:sticky lg:top-4 lg:self-start">
                    <UiCard title="Корзина">
                        <p
                            v-if="fieldErrors.lines"
                            class="mb-2 text-xs text-red-300"
                        >
                            {{ fieldErrors.lines }}
                        </p>
                        <p
                            v-if="cart.length === 0 && freeComplementLines.length === 0"
                            class="text-sm text-bo-muted"
                        >
                            Кликай позиции в меню.
                        </p>
                        <div
                            v-else
                            class="max-h-[50vh] space-y-2 overflow-y-auto"
                        >
                            <div
                                v-for="line in cart"
                                :key="line.key"
                                class="flex items-center justify-between gap-2 border border-bo-border bg-bo-field px-3 py-2"
                            >
                                <div class="min-w-0">
                                    <div class="truncate text-sm text-bo-fg">
                                        {{ line.name }}
                                    </div>
                                    <div class="text-xs text-bo-muted">
                                        {{ line.kind === 'set' ? 'Набор' : 'Товар' }}
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <button
                                        type="button"
                                        class="h-8 w-8 border border-bo-border text-bo-fg"
                                        @click="changeQty(line.key, -1)"
                                    >
                                        −
                                    </button>
                                    <span class="w-6 text-center text-sm">{{ line.quantity }}</span>
                                    <button
                                        type="button"
                                        class="h-8 w-8 border border-bo-border text-bo-fg"
                                        @click="changeQty(line.key, 1)"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <template v-if="freeComplementLines.length > 0">
                                <p class="pt-1 text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                                    Бесплатный комплект
                                </p>
                                <div
                                    v-for="(line, idx) in freeComplementLines"
                                    :key="`free-c-${line.product_id}-${idx}`"
                                    class="flex items-center justify-between gap-2 border border-dashed border-bo-border bg-white/[0.02] px-3 py-2"
                                >
                                    <div class="min-w-0">
                                        <div class="truncate text-sm text-bo-fg">
                                            {{ line.product_name }}
                                        </div>
                                        <div class="text-xs text-bo-muted">
                                            бесплатно × {{ line.quantity }}
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-sm text-bo-muted">0 ₽</span>
                                </div>
                            </template>
                        </div>
                        <div class="mt-3 flex justify-between border-t border-bo-border pt-3 text-sm">
                            <span class="text-bo-muted">{{ cartCount }} шт.</span>
                            <span class="font-medium">{{ rubles(totals.grand || totals.items) }}</span>
                        </div>
                        <p
                            v-if="quoteApi.loading"
                            class="mt-1 text-xs text-bo-muted"
                        >
                            Пересчёт…
                        </p>
                    </UiCard>
                </aside>

                <!-- mobile sticky cart strip -->
                <div
                    class="fixed inset-x-0 bottom-0 z-30 border-t border-bo-border bg-bo-elevated px-4 py-3 lg:hidden"
                >
                    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                        <div class="text-sm">
                            <span class="text-bo-muted">В заказе</span>
                            <span class="ml-2 font-medium text-bo-fg">{{ cartCount }} шт. · {{ rubles(totals.grand || totals.items) }}</span>
                        </div>
                        <UiButton
                            :disabled="cart.length === 0"
                            @click="goNext"
                        >
                            Далее
                        </UiButton>
                    </div>
                </div>
            </div>

            <!-- Шаг 3: Доставка / оплата -->
            <div
                v-else-if="stepId === 'fulfillment'"
                class="grid gap-4 lg:grid-cols-2"
            >
                <UiCard title="Доставка">
                    <div class="grid gap-3">
                        <UiFormField
                            label="Способ"
                            required
                        >
                            <select
                                v-model="form.method"
                                class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm text-bo-fg"
                            >
                                <option value="pickup">Самовывоз</option>
                                <option value="courier">Курьер</option>
                            </select>
                        </UiFormField>

                        <template v-if="form.method === 'courier'">
                            <DeliveryAddressFields
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
                                :map-open="mapOpen"
                                @update="onAddressUpdate"
                                @toggle-map="mapOpen = !mapOpen"
                            />
                            <DeliveryAddressMapPicker
                                v-if="mapOpen"
                                :city="form.city"
                                :street="form.street"
                                :house="form.house"
                                :latitude="form.lat"
                                :longitude="form.lng"
                                :bias-city="branchCity"
                                @update="onAddressUpdate"
                            />
                        </template>
                    </div>
                </UiCard>

                <UiCard title="Оплата">
                    <div class="grid gap-3">
                        <UiFormField
                            label="Оплата"
                            required
                            :error="fieldErrors.payment_method"
                        >
                            <select
                                v-model="form.payment_method"
                                class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm text-bo-fg"
                            >
                                <option value="cash">Наличные</option>
                                <option value="card_courier">Картой курьеру</option>
                            </select>
                        </UiFormField>
                        <UiFormField
                            v-if="form.payment_method === 'cash'"
                            label="Сдача с"
                        >
                            <UiInput
                                v-model="form.change_from"
                                type="number"
                                min="0"
                                step="1"
                            />
                        </UiFormField>

                        <div class="space-y-1 border border-bo-border bg-bo-field px-3 py-3 text-sm">
                            <div class="flex justify-between gap-2">
                                <span class="text-bo-muted">Товары</span>
                                <span>{{ rubles(totals.items) }}</span>
                            </div>
                            <div
                                v-if="deliveryFeeLabel"
                                class="flex justify-between gap-2"
                            >
                                <span class="text-bo-muted">Доставка</span>
                                <span>{{ deliveryFeeLabel }}</span>
                            </div>
                            <div class="flex justify-between gap-2 font-medium text-bo-fg">
                                <span>Итого</span>
                                <span>{{ rubles(totals.grand) }}</span>
                            </div>
                        </div>
                    </div>
                </UiCard>
            </div>

            <!-- Шаг 4: Подтверждение -->
            <UiCard
                v-else-if="stepId === 'confirm'"
                title="Подтверждение"
            >
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-bo-muted">Клиент</p>
                            <p class="text-bo-fg">{{ form.name }} · {{ form.phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-bo-muted">Получение</p>
                            <p class="text-bo-fg">{{ methodLabel }}</p>
                            <p
                                v-if="form.method === 'courier'"
                                class="text-bo-muted"
                            >
                                {{ form.street }}, {{ form.house }}
                                <template v-if="form.apartment">, кв. {{ form.apartment }}</template>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-bo-muted">Оплата</p>
                            <p class="text-bo-fg">
                                {{ paymentLabel }}
                                <template v-if="form.payment_method === 'cash' && form.change_from">
                                    · сдача с {{ form.change_from }}
                                </template>
                            </p>
                        </div>
                        <div
                            v-if="giftEligible || giftDecision !== null"
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <span class="text-bo-muted">
                                <template v-if="typeof giftDecision === 'number'">Подарок выбран</template>
                                <template v-else-if="giftDecision === 'declined'">Без подарка</template>
                                <template v-else>Доступен подарок</template>
                            </span>
                            <UiButton
                                class="!bg-white/10 !text-bo-fg"
                                @click="openGiftModal"
                            >
                                {{ giftDecision === null ? 'Выбрать подарок' : 'Изменить' }}
                            </UiButton>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-xs uppercase tracking-wide text-bo-muted">Основные линии</p>
                            <template v-if="quoteLines.length">
                                <div
                                    v-for="(line, index) in confirmMainLines"
                                    :key="`main-${line.product_id}-${index}`"
                                    class="flex justify-between gap-2 text-sm"
                                >
                                    <span class="truncate text-bo-fg">
                                        {{ line.product_name }} × {{ line.quantity }}
                                    </span>
                                    <span class="text-bo-muted">{{ rubles(line.line_total_rubles ?? line.unit_price_rubles) }}</span>
                                </div>
                                <p
                                    v-if="confirmMainLines.length === 0"
                                    class="text-sm text-bo-muted"
                                >
                                    Нет позиций
                                </p>
                            </template>
                            <template v-else>
                                <div
                                    v-for="line in cart"
                                    :key="line.key"
                                    class="flex justify-between gap-2 text-sm"
                                >
                                    <span>{{ line.name }} × {{ line.quantity }}</span>
                                </div>
                                <p
                                    v-if="cart.length === 0"
                                    class="text-sm text-bo-muted"
                                >
                                    Нет позиций
                                </p>
                            </template>
                        </div>

                        <div
                            v-if="confirmComplementLines.length > 0"
                            class="space-y-2"
                        >
                            <p class="text-xs uppercase tracking-wide text-bo-muted">Комплектные</p>
                            <div
                                v-for="(line, index) in confirmComplementLines"
                                :key="`complement-${line.product_id}-${index}`"
                                class="flex justify-between gap-2 text-sm"
                            >
                                <span class="truncate text-bo-fg">
                                    {{ line.product_name }} × {{ line.quantity }}
                                </span>
                                <span class="text-bo-muted">{{ rubles(line.line_total_rubles ?? line.unit_price_rubles) }}</span>
                            </div>
                        </div>

                        <div
                            v-if="confirmGiftLines.length > 0"
                            class="space-y-2"
                        >
                            <p class="text-xs uppercase tracking-wide text-bo-muted">Подарок</p>
                            <div
                                v-for="(line, index) in confirmGiftLines"
                                :key="`gift-${line.product_id}-${index}`"
                                class="flex justify-between gap-2 text-sm"
                            >
                                <span class="truncate text-bo-fg">
                                    {{ line.product_name }} × {{ line.quantity }}
                                </span>
                                <span class="text-bo-muted">{{ rubles(line.line_total_rubles ?? line.unit_price_rubles) }}</span>
                            </div>
                        </div>

                        <div class="mt-1 space-y-1 border-t border-bo-border pt-3 text-sm">
                            <div class="flex justify-between gap-2">
                                <span class="text-bo-muted">Товары</span>
                                <span>{{ rubles(totals.items) }}</span>
                            </div>
                            <div
                                v-if="deliveryFeeLabel"
                                class="flex justify-between gap-2"
                            >
                                <span class="text-bo-muted">Доставка</span>
                                <span>{{ deliveryFeeLabel }}</span>
                            </div>
                            <div class="flex justify-between gap-2 text-base font-semibold">
                                <span>Итого</span>
                                <span>{{ rubles(totals.grand) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </UiCard>

            <div
                class="flex flex-wrap items-center justify-between gap-3 border-t border-bo-border pt-4"
                :class="stepId === 'menu' ? 'pb-20 lg:pb-0' : ''"
            >
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    :disabled="stepIndex === 0"
                    @click="goBack"
                >
                    Назад
                </UiButton>
                <div class="flex flex-wrap gap-2">
                    <UiButton
                        v-if="stepId !== 'confirm'"
                        :class="stepId === 'menu' ? 'hidden lg:inline-flex' : ''"
                        :disabled="ensureApi.loading"
                        @click="goNext"
                    >
                        Далее
                    </UiButton>
                    <UiButton
                        v-if="stepId === 'confirm'"
                        :disabled="saveApi.loading || cart.length === 0"
                        @click="createOrder"
                    >
                        Создать заказ
                    </UiButton>
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
    </div>
</template>
