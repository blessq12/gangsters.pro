<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    addClientAddress,
    deleteClientAddress,
    getClient,
    setDefaultClientAddress,
    updateClientAddress,
    updateClientProfile,
} from "../../../api/clients.js";
import { getOrder } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import {
    formatAddress,
    isClosedOrder,
    orderStatusLabel,
    orderStatusVariant,
} from "../../../shell/domain/orderStage.js";
import { useOrderWorkspaceStore } from "../../../shell/store/orderWorkspaceStore.js";
import DeliveryAddressFields from "../components/order/DeliveryAddressFields.vue";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const workspace = useOrderWorkspaceStore();
const loadApi = useApi();
const profileApi = useApi();
const addressApi = useApi();
const repeatApi = useApi();

const clientId = computed(() => Number(route.params.id));

const profile = reactive({
    name: "",
    phone: "",
    email: "",
    birth_date: "",
    created_at: "",
});
const addresses = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const orders = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const loaded = ref(false);

const addressModalOpen = ref(false);
const addressEditingId = ref(/** @type {string|null} */ (null));
const addressForm = reactive({
    title: "",
    street: "",
    house: "",
    entrance: "",
    floor: "",
    apartment: "",
    comment: "",
    lat: /** @type {string|number} */ (""),
    lng: /** @type {string|number} */ (""),
    make_default: false,
});
const addressErrors = reactive(/** @type {Record<string, string>} */ ({}));
const deleteAddressTarget = ref(/** @type {{ id: string, label: string }|null} */ (null));
/** Сброс DaData-поиска при каждом открытии модалки. */
const addressFieldsKey = ref(0);

const orderColumns = [
    { key: "number", label: "Заказ", width: "140px" },
    { key: "status", label: "Статус", width: "130px" },
    { key: "total", label: "Сумма", width: "100px", align: "right" },
    { key: "created_at", label: "Создан", width: "170px" },
    { key: "actions", label: "", width: "56px", align: "center" },
];

const title = computed(() => {
    const name = String(profile.name ?? "").trim();
    if (name !== "") {
        return name;
    }
    const phone = String(profile.phone ?? "").trim();
    return phone !== "" ? phone : `Клиент #${clientId.value}`;
});

const deleteAddressMessage = computed(() => {
    const target = deleteAddressTarget.value;
    if (!target) {
        return "";
    }
    return `Удалить адрес «${target.label}»?`;
});

function formatDate(value) {
    if (!value) {
        return "—";
    }
    try {
        return new Date(value).toLocaleString("ru-RU");
    } catch {
        return String(value);
    }
}

function formatAddressLabel(address) {
    const formatted = formatAddress(address);
    if (formatted) {
        return formatted;
    }
    return address?.title || address?.id || "Адрес";
}

function clearAddressErrors() {
    Object.keys(addressErrors).forEach((key) => {
        delete addressErrors[key];
    });
}

/**
 * @param {unknown} value
 * @returns {number|null}
 */
function toCoordOrNull(value) {
    if (value === "" || value === null || value === undefined) {
        return null;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
}

function resetAddressForm() {
    addressForm.title = "";
    addressForm.street = "";
    addressForm.house = "";
    addressForm.entrance = "";
    addressForm.floor = "";
    addressForm.apartment = "";
    addressForm.comment = "";
    addressForm.lat = "";
    addressForm.lng = "";
    addressForm.make_default = addresses.value.length === 0;
    clearAddressErrors();
    addressFieldsKey.value += 1;
}

/**
 * @param {Record<string, unknown>} patch
 */
function onAddressFieldsUpdate(patch) {
    if (patch.street !== undefined) {
        addressForm.street = String(patch.street ?? "");
    }
    if (patch.house !== undefined) {
        addressForm.house = String(patch.house ?? "");
    }
    if (patch.entrance !== undefined) {
        addressForm.entrance = String(patch.entrance ?? "");
    }
    if (patch.floor !== undefined) {
        addressForm.floor = String(patch.floor ?? "");
    }
    if (patch.apartment !== undefined) {
        addressForm.apartment = String(patch.apartment ?? "");
    }
    if (patch.comment !== undefined) {
        addressForm.comment = String(patch.comment ?? "");
    }
    if (patch.latitude !== undefined) {
        addressForm.lat = patch.latitude === "" || patch.latitude === null
            ? ""
            : patch.latitude;
    }
    if (patch.longitude !== undefined) {
        addressForm.lng = patch.longitude === "" || patch.longitude === null
            ? ""
            : patch.longitude;
    }
}

/**
 * @param {Record<string, unknown>|null|undefined} payload
 */
function applyPayload(payload) {
    const client = payload?.client ?? {};
    profile.name = client.name ?? "";
    profile.phone = client.phone ?? "";
    profile.email = client.email ?? "";
    profile.birth_date = client.birth_date ?? "";
    profile.created_at = client.created_at ?? "";
    addresses.value = Array.isArray(client.addresses) ? [...client.addresses] : [];
    orders.value = (Array.isArray(payload?.orders) ? payload.orders : []).map((order) => ({
        id: order.id,
        number: order.number || `ORD-${String(order.id).padStart(4, "0")}`,
        status: order.status,
        total: `${order.total ?? 0} ₽`,
        created_at: formatDate(order.created_at),
        _raw: order,
    }));
    loaded.value = true;
}

async function load() {
    if (!clientId.value) {
        router.push({ name: "administrator-clients" });
        return;
    }
    try {
        const payload = await loadApi.run(() => getClient(clientId.value));
        if (!payload?.client) {
            toast.error("Клиент не найден");
            router.push({ name: "administrator-clients" });
            return;
        }
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить клиента");
        router.push({ name: "administrator-clients" });
    }
}

function goBack() {
    router.push({ name: "administrator-clients" });
}

async function saveProfile() {
    try {
        const payload = await profileApi.run(() => updateClientProfile(clientId.value, {
            name: profile.name,
            phone: profile.phone,
            email: profile.email || null,
            birth_date: profile.birth_date || null,
        }));
        if (payload?.client) {
            profile.name = payload.client.name ?? profile.name;
            profile.phone = payload.client.phone ?? profile.phone;
            profile.email = payload.client.email ?? "";
            profile.birth_date = payload.client.birth_date ?? "";
            if (Array.isArray(payload.client.addresses)) {
                addresses.value = [...payload.client.addresses];
            }
        }
        toast.success("Данные клиента сохранены");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сохранить");
    }
}

async function copyPhone() {
    const phone = String(profile.phone ?? "").trim();
    if (!phone) {
        toast.error("Телефон не указан");
        return;
    }
    try {
        await navigator.clipboard.writeText(phone);
        toast.success("Телефон скопирован");
    } catch {
        toast.error("Не удалось скопировать");
    }
}

/**
 * @param {{ id: number|string }} row
 */
function openOrder(row) {
    if (!row?.id) {
        return;
    }
    router.push({ name: "administrator-order", params: { id: String(row.id) } });
}

/**
 * @param {MouseEvent} event
 * @param {{ id: number|string, status?: string }} row
 */
async function repeatOrder(event, row) {
    event.stopPropagation();
    if (!row?.id || !isClosedOrder(row.status)) {
        return;
    }
    try {
        const order = await repeatApi.run(() => getOrder(row.id));
        workspace.hydrateRepeat(order);
        router.push({ name: "administrator-order-new" });
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось подготовить повтор");
    }
}

function openCreateAddress() {
    addressEditingId.value = null;
    resetAddressForm();
    addressModalOpen.value = true;
}

/**
 * @param {Record<string, unknown>} address
 */
function openEditAddress(address) {
    addressEditingId.value = String(address.id);
    addressForm.title = address.title ?? "";
    addressForm.street = address.street ?? "";
    addressForm.house = address.house ?? "";
    addressForm.entrance = address.entrance ?? "";
    addressForm.floor = address.floor ?? "";
    addressForm.apartment = address.apartment ?? "";
    addressForm.comment = address.comment ?? "";
    addressForm.lat = address.lat ?? "";
    addressForm.lng = address.lng ?? "";
    addressForm.make_default = Boolean(address.is_default);
    clearAddressErrors();
    addressFieldsKey.value += 1;
    addressModalOpen.value = true;
}

function validateAddressForm() {
    clearAddressErrors();
    if (!String(addressForm.street).trim()) {
        addressErrors.street = "Укажите улицу";
    }
    if (!String(addressForm.house).trim()) {
        addressErrors.house = "Укажите дом";
    }
    const lat = toCoordOrNull(addressForm.lat);
    const lng = toCoordOrNull(addressForm.lng);
    if (lat === null || lng === null) {
        toast.error("У адреса нет координат — выбери адрес из подсказок");
        return false;
    }
    return Object.keys(addressErrors).length === 0;
}

async function saveAddress() {
    if (!validateAddressForm()) {
        return;
    }
    const body = {
        title: addressForm.title || null,
        street: addressForm.street,
        house: addressForm.house,
        entrance: addressForm.entrance || null,
        floor: addressForm.floor || null,
        apartment: addressForm.apartment || null,
        comment: addressForm.comment || null,
        lat: toCoordOrNull(addressForm.lat),
        lng: toCoordOrNull(addressForm.lng),
        make_default: Boolean(addressForm.make_default),
    };
    try {
        if (addressEditingId.value) {
            await addressApi.run(() => updateClientAddress(
                clientId.value,
                addressEditingId.value,
                body,
            ));
        } else {
            await addressApi.run(() => addClientAddress(clientId.value, body));
        }
        addressModalOpen.value = false;
        toast.success("Адрес сохранён");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сохранить адрес");
    }
}

/**
 * @param {Record<string, unknown>} address
 */
async function makeDefault(address) {
    try {
        const payload = await addressApi.run(() => setDefaultClientAddress(
            clientId.value,
            String(address.id),
        ));
        if (Array.isArray(payload?.client?.addresses)) {
            addresses.value = [...payload.client.addresses];
        } else {
            await load();
        }
        toast.success("Адрес сделан основным");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сменить основной адрес");
    }
}

async function confirmDeleteAddress() {
    const target = deleteAddressTarget.value;
    deleteAddressTarget.value = null;
    if (!target?.id) {
        return;
    }
    try {
        const payload = await addressApi.run(() => deleteClientAddress(
            clientId.value,
            target.id,
        ));
        if (Array.isArray(payload?.client?.addresses)) {
            addresses.value = [...payload.client.addresses];
        } else {
            await load();
        }
        toast.success("Адрес удалён");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось удалить адрес");
    }
}

watch(() => route.params.id, load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="goBack"
                >
                    К клиентам
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    :disabled="!profile.phone || profileApi.loading"
                    @click="copyPhone"
                >
                    Копировать телефон
                </UiButton>
                <UiButton
                    :disabled="profileApi.loading || loadApi.loading || !loaded"
                    @click="saveProfile"
                >
                    Сохранить данные
                </UiButton>
            </div>
        </div>

        <p
            v-if="loadApi.loading && !loaded"
            class="text-sm text-bo-muted"
        >
            Загрузка…
        </p>

        <template v-else-if="loaded">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-1">
                    <UiCard title="Данные клиента">
                        <div class="grid gap-3">
                            <UiFormField label="Имя">
                                <UiInput v-model="profile.name" />
                            </UiFormField>
                            <UiFormField label="Телефон">
                                <UiInput
                                    v-model="profile.phone"
                                    type="tel"
                                />
                            </UiFormField>
                            <UiFormField label="Email">
                                <UiInput
                                    v-model="profile.email"
                                    type="email"
                                />
                            </UiFormField>
                            <UiFormField label="Дата рождения">
                                <UiInput
                                    v-model="profile.birth_date"
                                    type="date"
                                />
                            </UiFormField>
                            <p class="text-xs text-bo-muted">
                                Создан: {{ formatDate(profile.created_at) }}
                            </p>
                        </div>
                    </UiCard>

                    <UiCard title="Адреса">
                        <template #actions>
                            <UiButton
                                class="!h-9 !px-3"
                                @click="openCreateAddress"
                            >
                                + Адрес
                            </UiButton>
                        </template>

                        <ul
                            v-if="addresses.length"
                            class="space-y-3"
                        >
                            <li
                                v-for="address in addresses"
                                :key="address.id"
                                class="space-y-2 border border-bo-border bg-black/20 px-3 py-2"
                            >
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="font-medium text-bo-fg">
                                        {{ formatAddressLabel(address) }}
                                    </span>
                                    <UiBadge
                                        v-if="address.is_default"
                                        variant="success"
                                        text="основной"
                                    />
                                </div>
                                <p
                                    v-if="address.title"
                                    class="text-xs text-bo-muted"
                                >
                                    {{ address.title }}
                                </p>
                                <p
                                    v-if="address.comment"
                                    class="text-xs text-bo-muted"
                                >
                                    {{ address.comment }}
                                </p>
                                <div class="flex flex-wrap gap-1">
                                    <UiButton
                                        class="!h-8 !px-2 !text-xs"
                                        :disabled="addressApi.loading || Boolean(address.is_default)"
                                        @click="makeDefault(address)"
                                    >
                                        Основной
                                    </UiButton>
                                    <UiButton
                                        class="!h-8 !px-2 !text-xs"
                                        :disabled="addressApi.loading"
                                        @click="openEditAddress(address)"
                                    >
                                        Изменить
                                    </UiButton>
                                    <UiButton
                                        class="!h-8 !px-2 !text-xs"
                                        :disabled="addressApi.loading"
                                        @click="deleteAddressTarget = {
                                            id: String(address.id),
                                            label: formatAddressLabel(address),
                                        }"
                                    >
                                        Удалить
                                    </UiButton>
                                </div>
                            </li>
                        </ul>
                        <p
                            v-else
                            class="text-sm text-bo-muted"
                        >
                            Адресов нет
                        </p>
                    </UiCard>
                </div>

                <div class="lg:col-span-2">
                    <UiCard title="История заказов">
                        <UiTable
                            :columns="orderColumns"
                            :rows="orders"
                            empty-text="Нет заказов"
                            @row-click="openOrder"
                        >
                            <template #cell-status="{ value }">
                                <UiBadge
                                    :variant="orderStatusVariant(value)"
                                    :text="orderStatusLabel(value)"
                                />
                            </template>
                            <template #cell-actions="{ row }">
                                <button
                                    v-if="isClosedOrder(row.status)"
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center border border-bo-border bg-white/10 text-bo-fg transition-colors hover:bg-white/[0.08] disabled:opacity-40"
                                    :disabled="repeatApi.loading"
                                    aria-label="Повторить заказ"
                                    title="Повторить заказ"
                                    @click="repeatOrder($event, row)"
                                >
                                    <ShellIcon
                                        name="undo"
                                        class="h-4 w-4"
                                    />
                                </button>
                                <span
                                    v-else
                                    class="text-bo-muted"
                                >—</span>
                            </template>
                        </UiTable>
                    </UiCard>
                </div>
            </div>
        </template>

        <UiModal
            :open="addressModalOpen"
            :title="addressEditingId ? 'Изменить адрес' : 'Новый адрес'"
            @close="addressModalOpen = false"
        >
            <div class="space-y-4">
                <UiFormField label="Название">
                    <UiInput
                        v-model="addressForm.title"
                        placeholder="Дом / Работа"
                    />
                </UiFormField>

                <DeliveryAddressFields
                    :key="addressFieldsKey"
                    :street="addressForm.street"
                    :house="addressForm.house"
                    :entrance="addressForm.entrance"
                    :floor="addressForm.floor"
                    :apartment="addressForm.apartment"
                    :comment="addressForm.comment"
                    :latitude="addressForm.lat"
                    :longitude="addressForm.lng"
                    :street-error="addressErrors.street || ''"
                    :house-error="addressErrors.house || ''"
                    @update="onAddressFieldsUpdate"
                />

                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="addressForm.make_default"
                        type="checkbox"
                        class="accent-bo-accent"
                    >
                    Сделать основным
                </label>
            </div>
            <template #footer>
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="addressModalOpen = false"
                >
                    Отмена
                </UiButton>
                <UiButton
                    :disabled="addressApi.loading"
                    @click="saveAddress"
                >
                    Сохранить
                </UiButton>
            </template>
        </UiModal>

        <UiConfirmDialog
            :open="deleteAddressTarget !== null"
            title="Удалить адрес"
            :message="deleteAddressMessage"
            @confirm="confirmDeleteAddress"
            @cancel="deleteAddressTarget = null"
        />
    </div>
</template>
