<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { getCatalog } from "../../../api/catalog.js";
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
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import ClientSearchField from "../components/order/ClientSearchField.vue";
import DeliveryAddressMapPicker from "../components/order/DeliveryAddressMapPicker.vue";
import GiftPickModal from "../components/order/GiftPickModal.vue";
import PosMenuPanel from "../components/order/PosMenuPanel.vue";

const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);

const menuApi = useApi();
const quoteApi = useApi();
const saveApi = useApi();

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

const categories = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const cart = ref(/** @type {Array<{ key: string, id: number, kind: string, name: string, quantity: number }>} */ ([]));
const quote = ref(/** @type {Record<string, unknown>|null} */ (null));
const fieldErrors = reactive({});

/** null | number | 'declined' */
const giftDecision = ref(/** @type {null|number|'declined'} */ (null));
const giftModalOpen = ref(false);
const giftPromptedForEligible = ref(false);
/** Выезжающая POS-панель: не раздувает вертикальный скролл формы. */
const posOpen = ref(false);

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

function openPos() {
    posOpen.value = true;
}

function closePos() {
    posOpen.value = false;
}

function togglePos() {
    posOpen.value = !posOpen.value;
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
 * @param {{ city: string, street: string, house: string, latitude: number, longitude: number }} address
 */
function onMapUpdate(address) {
    form.city = address.city || "";
    form.street = address.street || "";
    form.house = address.house || "";
    form.lat = String(address.latitude ?? "");
    form.lng = String(address.longitude ?? "");
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
        client: form.clientId
            ? {
                kind: "client",
                id: form.clientId,
                name: form.name.trim() || null,
                phone: form.phone.trim() || null,
            }
            : {
                kind: "guest",
                name: form.name.trim(),
                phone: form.phone.trim(),
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
    if (!branchId.value || cart.value.length === 0) {
        quote.value = null;
        return;
    }
    if (form.method === "courier") {
        const hasCoords = form.lat !== "" && form.lng !== "";
        const hasStreetHouse =
            String(form.street).trim() !== "" && String(form.house).trim() !== "";
        if (!hasCoords && !hasStreetHouse) {
            // Адрес ещё не выбран — не дергаем quote и не орём тостом.
            quote.value = null;
            return;
        }
    }
    if (!form.clientId && (!form.name.trim() || !form.phone.trim())) {
        // quote всё равно полезен без клиента — подставим черновик
    }

    const body = buildCheckoutBody();
    if (!body.client.name) {
        body.client.name = "Гость";
    }
    if (!body.client.phone) {
        body.client.phone = "+70000000000";
    }

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

function validate() {
    clearErrors();
    if (!branchId.value) {
        fieldErrors.branch = "Нет филиала смены";
    }
    if (!form.clientId) {
        if (!String(form.name).trim()) {
            fieldErrors.name = "Обязательное поле";
        }
        if (!String(form.phone).trim()) {
            fieldErrors.phone = "Обязательное поле";
        }
    }
    if (cart.value.length === 0) {
        fieldErrors.lines = "Добавь позиции из меню";
    }
    if (form.method === "courier") {
        if (!String(form.street).trim() || !String(form.house).trim()) {
            fieldErrors.address = "Выбери адрес на карте";
        }
        if (form.lat === "" || form.lng === "") {
            fieldErrors.address = "Нужны координаты с карты";
        }
    }
    if (!form.payment_method) {
        fieldErrors.payment_method = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function createOrder() {
    if (!validate()) {
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
        client_request_id: `admin-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
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

        // Фоллбек: плоский список доступного меню, если категорий нет.
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
        form.name,
        form.phone,
        giftDecision.value,
    ],
    () => {
        scheduleQuote();
    },
    { deep: false },
);

watch(giftEligible, (eligible, wasEligible) => {
    if (eligible && !wasEligible && giftDecision.value === null && !giftPromptedForEligible.value) {
        giftPromptedForEligible.value = true;
        giftModalOpen.value = true;
    }
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
            <div class="flex flex-wrap gap-2">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="router.push({ name: 'administrator-live-orders' })"
                >
                    К списку
                </UiButton>
                <UiButton
                    v-if="branchId"
                    class="!bg-white/10 !text-bo-fg"
                    @click="togglePos"
                >
                    {{ posOpen ? "Свернуть меню" : "Меню" }}
                    <span
                        v-if="cartCount > 0"
                        class="ml-1 opacity-80"
                    >({{ cartCount }})</span>
                </UiButton>
                <UiButton
                    :disabled="!branchId || saveApi.loading || cart.length === 0"
                    @click="createOrder"
                >
                    Создать заказ
                </UiButton>
            </div>
        </div>

        <p
            v-if="!branchId"
            class="border border-bo-border bg-bo-elevated px-4 py-6 text-sm text-bo-muted"
        >
            У сотрудника не указан филиал смены — создать заказ нельзя.
        </p>

        <template v-else>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-1">
                    <UiCard title="Клиент">
                        <ClientSearchField
                            v-model:name="form.name"
                            v-model:phone="form.phone"
                            :client-id="form.clientId"
                            :name-error="fieldErrors.name"
                            :phone-error="fieldErrors.phone"
                            @select-client="onSelectClient"
                            @clear-client="onClearClient"
                        />
                    </UiCard>

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
                                <p
                                    v-if="fieldErrors.address"
                                    class="text-xs text-red-300"
                                >
                                    {{ fieldErrors.address }}
                                </p>
                                <DeliveryAddressMapPicker
                                    :city="form.city"
                                    :street="form.street"
                                    :house="form.house"
                                    :latitude="form.lat"
                                    :longitude="form.lng"
                                    @update="onMapUpdate"
                                />
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <UiFormField label="Подъезд">
                                        <UiInput v-model="form.entrance" />
                                    </UiFormField>
                                    <UiFormField label="Этаж">
                                        <UiInput v-model="form.floor" />
                                    </UiFormField>
                                    <UiFormField label="Квартира">
                                        <UiInput v-model="form.apartment" />
                                    </UiFormField>
                                </div>
                                <UiFormField label="Комментарий к адресу">
                                    <UiInput v-model="form.comment" />
                                </UiFormField>
                            </template>
                        </div>
                    </UiCard>

                    <UiCard title="Оплата и итог">
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
                                <p
                                    v-if="quoteApi.loading"
                                    class="text-xs text-bo-muted"
                                >
                                    Пересчёт…
                                </p>
                            </div>

                            <div
                                v-if="giftEligible || giftDecision !== null"
                                class="flex flex-wrap items-center justify-between gap-2 text-sm"
                            >
                                <span class="text-bo-muted">
                                    <template v-if="typeof giftDecision === 'number'">
                                        Подарок выбран
                                    </template>
                                    <template v-else-if="giftDecision === 'declined'">
                                        Без подарка
                                    </template>
                                    <template v-else>
                                        Доступен подарок
                                    </template>
                                </span>
                                <UiButton
                                    class="!bg-white/10 !text-bo-fg"
                                    @click="openGiftModal"
                                >
                                    {{ giftDecision === null ? 'Выбрать подарок' : 'Изменить' }}
                                </UiButton>
                            </div>
                        </div>
                    </UiCard>
                </div>

                <div class="lg:col-span-2">
                    <UiCard title="Корзина">
                        <template #actions>
                            <UiButton
                                class="!bg-white/10 !text-bo-fg"
                                @click="openPos"
                            >
                                {{ cart.length === 0 ? "+ Добавить из меню" : "Открыть меню" }}
                            </UiButton>
                        </template>
                        <p
                            v-if="fieldErrors.lines"
                            class="mb-2 text-xs text-red-300"
                        >
                            {{ fieldErrors.lines }}
                        </p>
                        <p
                            v-if="cart.length === 0"
                            class="text-sm text-bo-muted"
                        >
                            Корзина пуста — открой меню и добавь позиции.
                        </p>
                        <div
                            v-else
                            class="space-y-2"
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
                                <div class="flex items-center gap-2">
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
                                    <button
                                        type="button"
                                        class="text-xs text-bo-muted hover:text-bo-fg"
                                        @click="removeLine(line.key)"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="quoteLines.length"
                            class="mt-4 space-y-1 border-t border-bo-border pt-3"
                        >
                            <p class="text-xs font-medium uppercase tracking-wide text-bo-muted">
                                Расчёт
                            </p>
                            <div
                                v-for="(line, index) in quoteLines"
                                :key="`${line.product_id}-${index}`"
                                class="flex justify-between gap-2 text-xs text-bo-muted"
                            >
                                <span class="truncate">
                                    {{ line.product_name }} × {{ line.quantity }}
                                    <span v-if="line.payload?.kind && line.payload.kind !== 'regular'">
                                        ({{ line.payload.kind === 'complement' ? 'комплект' : line.payload.kind === 'gift' ? 'подарок' : line.payload.kind === 'set' ? 'набор' : line.payload.kind }})
                                    </span>
                                </span>
                                <span>{{ rubles(line.line_total_rubles ?? line.unit_price_rubles) }}</span>
                            </div>
                        </div>
                    </UiCard>
                </div>
            </div>

            <Teleport to="body">
                <div
                    v-if="posOpen"
                    class="fixed inset-0 z-40"
                >
                    <button
                        type="button"
                        class="absolute inset-0 bg-black/55 backdrop-blur-sm"
                        aria-label="Свернуть меню"
                        @click="closePos"
                    />
                    <aside
                        class="absolute inset-y-0 right-0 flex w-full max-w-xl flex-col border-l border-bo-border bg-bo-elevated shadow-bo-lg"
                        role="dialog"
                        aria-modal="true"
                        aria-label="Меню филиала"
                    >
                        <header class="flex items-center justify-between gap-3 border-b border-bo-border px-5 py-4">
                            <div>
                                <h2 class="text-lg font-semibold text-bo-fg">
                                    Меню
                                </h2>
                                <p class="text-xs text-bo-muted">
                                    Клик по позиции добавляет в корзину · в заказе {{ cartCount }} шт.
                                </p>
                            </div>
                            <UiButton
                                class="!bg-white/10 !text-bo-fg"
                                @click="closePos"
                            >
                                Свернуть
                            </UiButton>
                        </header>
                        <div class="min-h-0 flex-1 overflow-hidden p-5">
                            <div class="h-full">
                                <PosMenuPanel
                                    :categories="categories"
                                    :loading="menuApi.loading"
                                    @add="addToCart"
                                />
                            </div>
                        </div>
                    </aside>
                </div>
            </Teleport>
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
