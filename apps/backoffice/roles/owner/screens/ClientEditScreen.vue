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
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
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
const loadApi = useApi();
const profileApi = useApi();
const addressApi = useApi();

const clientId = computed(() => Number(route.params.id));

const profile = reactive({
    name: "",
    phone: "",
    email: "",
    birth_date: "",
});
const addresses = ref(/** @type {object[]} */ ([]));
const orders = ref(/** @type {object[]} */ ([]));

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
    make_default: false,
});
const addressErrors = reactive(/** @type {Record<string, string>} */ ({}));
const deleteAddressTarget = ref(/** @type {{ id: string, label: string }|null} */ (null));

const orderColumns = [
    { key: "number", label: "Заказ", width: "140px" },
    { key: "status", label: "Статус", width: "130px" },
    { key: "total", label: "Сумма", width: "100px", align: "right" },
    { key: "created_at", label: "Создан", width: "170px" },
    { key: "actions", label: "", width: "120px" },
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
    const parts = [
        address?.street,
        address?.house ? `д. ${address.house}` : null,
        address?.apartment ? `кв. ${address.apartment}` : null,
    ].filter(Boolean);
    return parts.join(", ") || address?.title || address?.id || "Адрес";
}

function clearAddressErrors() {
    Object.keys(addressErrors).forEach((key) => {
        delete addressErrors[key];
    });
}

function applyPayload(payload) {
    const client = payload?.client ?? {};
    profile.name = client.name ?? "";
    profile.phone = client.phone ?? "";
    profile.email = client.email ?? "";
    profile.birth_date = client.birth_date ?? "";
    addresses.value = Array.isArray(client.addresses) ? [...client.addresses] : [];
    orders.value = (Array.isArray(payload?.orders) ? payload.orders : []).map((order) => ({
        id: order.id,
        number: order.number || `ORD-${String(order.id).padStart(4, "0")}`,
        status: order.status,
        total: `${order.total ?? 0} ₽`,
        created_at: formatDate(order.created_at),
    }));
}

async function load() {
    if (!clientId.value) {
        return;
    }
    try {
        const payload = await loadApi.run(() => getClient(clientId.value));
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить клиента");
        router.push({ name: "owner-clients" });
    }
}

async function saveProfile() {
    try {
        const payload = await profileApi.run(() =>
            updateClientProfile(clientId.value, {
                name: profile.name,
                phone: profile.phone,
                email: profile.email || null,
                birth_date: profile.birth_date || null,
            }),
        );
        if (payload?.client) {
            profile.name = payload.client.name ?? profile.name;
            profile.phone = payload.client.phone ?? profile.phone;
            profile.email = payload.client.email ?? "";
            profile.birth_date = payload.client.birth_date ?? "";
            if (Array.isArray(payload.client.addresses)) {
                addresses.value = [...payload.client.addresses];
            }
        }
        toast.success("Контакты сохранены");
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

function openOrder(row) {
    if (!row?.id) {
        return;
    }
    router.push({ name: "owner-order", params: { id: String(row.id) } });
}

function openCreateAddress() {
    addressEditingId.value = null;
    addressForm.title = "";
    addressForm.street = "";
    addressForm.house = "";
    addressForm.entrance = "";
    addressForm.floor = "";
    addressForm.apartment = "";
    addressForm.comment = "";
    addressForm.make_default = addresses.value.length === 0;
    clearAddressErrors();
    addressModalOpen.value = true;
}

function openEditAddress(address) {
    addressEditingId.value = String(address.id);
    addressForm.title = address.title ?? "";
    addressForm.street = address.street ?? "";
    addressForm.house = address.house ?? "";
    addressForm.entrance = address.entrance ?? "";
    addressForm.floor = address.floor ?? "";
    addressForm.apartment = address.apartment ?? "";
    addressForm.comment = address.comment ?? "";
    addressForm.make_default = Boolean(address.is_default);
    clearAddressErrors();
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
        make_default: Boolean(addressForm.make_default),
    };
    try {
        if (addressEditingId.value) {
            await addressApi.run(() =>
                updateClientAddress(clientId.value, addressEditingId.value, body),
            );
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

async function makeDefault(address) {
    try {
        const payload = await addressApi.run(() =>
            setDefaultClientAddress(clientId.value, String(address.id)),
        );
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
        const payload = await addressApi.run(() => deleteClientAddress(clientId.value, target.id));
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
                    @click="router.push({ name: 'owner-clients' })"
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
                <UiButton :disabled="profileApi.loading || loadApi.loading" @click="saveProfile">
                    Сохранить контакты
                </UiButton>
            </div>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">Загрузка…</p>

        <template v-else>
            <UiCard title="Контакты">
                <div class="grid gap-4 sm:grid-cols-2">
                    <UiFormField label="Имя">
                        <UiInput v-model="profile.name" />
                    </UiFormField>
                    <UiFormField label="Телефон">
                        <UiInput v-model="profile.phone" type="tel" />
                    </UiFormField>
                    <UiFormField label="Email">
                        <UiInput v-model="profile.email" type="email" />
                    </UiFormField>
                    <UiFormField label="Дата рождения">
                        <UiInput v-model="profile.birth_date" type="date" />
                    </UiFormField>
                </div>
            </UiCard>

            <UiCard title="Адреса">
                <template #actions>
                    <UiButton class="!h-9 !px-3" @click="openCreateAddress">+ Адрес</UiButton>
                </template>

                <div v-if="addresses.length === 0" class="text-sm text-bo-muted">
                    Адресов пока нет
                </div>
                <ul v-else class="space-y-3">
                    <li
                        v-for="address in addresses"
                        :key="address.id"
                        class="flex flex-wrap items-start justify-between gap-3 border border-bo-border bg-white/[0.02] px-4 py-3"
                    >
                        <div class="min-w-0 space-y-1 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium text-bo-fg">
                                    {{ formatAddressLabel(address) }}
                                </span>
                                <UiBadge
                                    v-if="address.is_default"
                                    variant="success"
                                    text="основной"
                                />
                            </div>
                            <p v-if="address.title" class="text-bo-muted">
                                {{ address.title }}
                            </p>
                            <p v-if="address.comment" class="text-bo-muted">
                                {{ address.comment }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <UiButton
                                class="!h-9 !px-2.5"
                                :disabled="addressApi.loading || address.is_default"
                                @click="makeDefault(address)"
                            >
                                Основной
                            </UiButton>
                            <UiButton
                                class="!h-9 !px-2.5"
                                :disabled="addressApi.loading"
                                @click="openEditAddress(address)"
                            >
                                Изменить
                            </UiButton>
                            <UiButton
                                class="!h-9 !px-2.5"
                                :disabled="addressApi.loading"
                                @click="
                                    deleteAddressTarget = {
                                        id: String(address.id),
                                        label: formatAddressLabel(address),
                                    }
                                "
                            >
                                Удалить
                            </UiButton>
                        </div>
                    </li>
                </ul>
            </UiCard>

            <UiCard title="История заказов">
                <UiTable :columns="orderColumns" :rows="orders" empty-text="Нет заказов">
                    <template #cell-status="{ value }">
                        <UiBadge variant="neutral" :text="String(value)" />
                    </template>
                    <template #cell-actions="{ row }">
                        <UiButton class="!h-9 !px-2.5" @click="openOrder(row)">Открыть</UiButton>
                    </template>
                </UiTable>
            </UiCard>
        </template>

        <UiModal
            :open="addressModalOpen"
            :title="addressEditingId ? 'Изменить адрес' : 'Новый адрес'"
            @close="addressModalOpen = false"
        >
            <div class="grid gap-3 sm:grid-cols-2">
                <UiFormField label="Название" class="sm:col-span-2">
                    <UiInput v-model="addressForm.title" placeholder="Дом / Работа" />
                </UiFormField>
                <UiFormField label="Улица" required :error="addressErrors.street">
                    <UiInput v-model="addressForm.street" />
                </UiFormField>
                <UiFormField label="Дом" required :error="addressErrors.house">
                    <UiInput v-model="addressForm.house" />
                </UiFormField>
                <UiFormField label="Подъезд">
                    <UiInput v-model="addressForm.entrance" />
                </UiFormField>
                <UiFormField label="Этаж">
                    <UiInput v-model="addressForm.floor" />
                </UiFormField>
                <UiFormField label="Квартира">
                    <UiInput v-model="addressForm.apartment" />
                </UiFormField>
                <UiFormField label="Комментарий" class="sm:col-span-2">
                    <UiInput v-model="addressForm.comment" />
                </UiFormField>
                <label class="flex items-center gap-2 text-sm sm:col-span-2">
                    <input
                        v-model="addressForm.make_default"
                        type="checkbox"
                        class="accent-bo-accent"
                    />
                    Сделать основным
                </label>
            </div>
            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="addressModalOpen = false">
                    Отмена
                </UiButton>
                <UiButton :disabled="addressApi.loading" @click="saveAddress">Сохранить</UiButton>
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
