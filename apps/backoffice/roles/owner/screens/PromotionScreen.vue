<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import {
    assignProductRole,
    getPromotionPolicy,
    storePromotionPolicy,
    syncZones,
    updatePromotionPolicy,
} from "../../../api/promotion.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const tab = ref("policy");
const policyExists = ref(false);
const roles = ref([]);
const zones = ref([]);

const weekdayOptions = [
    { value: 1, label: "пн" },
    { value: 2, label: "вт" },
    { value: 3, label: "ср" },
    { value: 4, label: "чт" },
    { value: 5, label: "пт" },
    { value: 6, label: "сб" },
    { value: 7, label: "вс" },
];

const policyForm = reactive({
    gift_pickup_min_order_kopecks: "0",
    gift_courier_min_order_kopecks: "0",
    gift_courier_weekdays: /** @type {number[]} */ ([]),
    gift_benefit_active: false,
    delivery_free_threshold_kopecks: "0",
    delivery_outside_zone_surcharge_kopecks: "0",
    delivery_benefit_active: false,
    complement_set_benefit_active: false,
    complement_set_rolls_per_set: "1",
    complement_set_max_sets: "1",
});
const policyErrors = reactive({});

const roleModalOpen = ref(false);
const roleErrors = reactive({});
const roleForm = reactive({
    product_id: "",
    role: "roll",
    name: "",
    price_rubles: "",
});

const tabs = [
    { id: "policy", label: "Политика" },
    { id: "roles", label: "Роли товаров" },
    { id: "zones", label: "Зоны" },
];

const roleColumns = [
    { key: "product_id", label: "Product ID", width: "100px" },
    { key: "name", label: "Название" },
    { key: "price", label: "Цена", width: "90px" },
    { key: "role", label: "Роль", width: "120px" },
];

const zoneColumns = [
    { key: "id", label: "ID", width: "120px" },
    { key: "name", label: "Название" },
    { key: "fee", label: "Тариф", width: "90px" },
    { key: "is_remote", label: "Удалённая", width: "110px" },
];

const roleRows = computed(() =>
    roles.value.map((row) => ({
        ...row,
        price: `${Math.floor(Number(row.price_kopecks ?? 0) / 100)} ₽`,
    })),
);

const zoneRows = computed(() =>
    zones.value.map((row) => ({
        ...row,
        fee: `${Math.floor(Number(row.fee_kopecks ?? 0) / 100)} ₽`,
    })),
);

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

function applyPolicy(payload) {
    policyExists.value = Boolean(payload?.gift || payload?.delivery || payload?.complement);
    policyForm.gift_pickup_min_order_kopecks = String(
        payload?.gift?.pickup_min_order_kopecks ?? 0,
    );
    policyForm.gift_courier_min_order_kopecks = String(
        payload?.gift?.courier_min_order_kopecks ?? 0,
    );
    policyForm.gift_courier_weekdays = Array.isArray(payload?.gift?.courier_weekdays)
        ? [...payload.gift.courier_weekdays]
        : [];
    policyForm.gift_benefit_active = Boolean(payload?.gift?.is_active);
    policyForm.delivery_free_threshold_kopecks = String(
        payload?.delivery?.free_delivery_threshold_kopecks ?? 0,
    );
    policyForm.delivery_outside_zone_surcharge_kopecks = String(
        payload?.delivery?.outside_zone_surcharge_kopecks ?? 0,
    );
    policyForm.delivery_benefit_active = Boolean(payload?.delivery?.is_active);
    policyForm.complement_set_benefit_active = Boolean(payload?.complement?.is_active);
    policyForm.complement_set_rolls_per_set = String(payload?.complement?.rolls_per_set ?? 1);
    policyForm.complement_set_max_sets = String(payload?.complement?.max_sets ?? 1);
    roles.value = payload?.roles ?? [];
    zones.value = payload?.zones ?? [];
}

async function loadPolicy() {
    try {
        const payload = await loadApi.run(() => getPromotionPolicy());
        applyPolicy(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить политику");
    }
}

function toggleWeekday(day) {
    const idx = policyForm.gift_courier_weekdays.indexOf(day);
    if (idx >= 0) {
        policyForm.gift_courier_weekdays.splice(idx, 1);
    } else {
        policyForm.gift_courier_weekdays.push(day);
        policyForm.gift_courier_weekdays.sort((a, b) => a - b);
    }
}

function validatePolicy() {
    clearErrors(policyErrors);
    const requiredNums = [
        "gift_pickup_min_order_kopecks",
        "gift_courier_min_order_kopecks",
        "delivery_free_threshold_kopecks",
        "delivery_outside_zone_surcharge_kopecks",
        "complement_set_rolls_per_set",
        "complement_set_max_sets",
    ];
    requiredNums.forEach((key) => {
        if (policyForm[key] === "" || Number.isNaN(Number(policyForm[key]))) {
            policyErrors[key] = "Число";
        }
    });
    return Object.keys(policyErrors).length === 0;
}

async function savePolicy() {
    if (!validatePolicy()) {
        toast.error("Проверьте поля");
        return;
    }

    const body = {
        gift_pickup_min_order_kopecks: Number(policyForm.gift_pickup_min_order_kopecks),
        gift_courier_min_order_kopecks: Number(policyForm.gift_courier_min_order_kopecks),
        gift_courier_weekdays: [...policyForm.gift_courier_weekdays],
        gift_benefit_active: Boolean(policyForm.gift_benefit_active),
        delivery_free_threshold_kopecks: Number(policyForm.delivery_free_threshold_kopecks),
        delivery_outside_zone_surcharge_kopecks: Number(
            policyForm.delivery_outside_zone_surcharge_kopecks,
        ),
        delivery_benefit_active: Boolean(policyForm.delivery_benefit_active),
        complement_set_benefit_active: Boolean(policyForm.complement_set_benefit_active),
        complement_set_rolls_per_set: Number(policyForm.complement_set_rolls_per_set),
        complement_set_max_sets: Number(policyForm.complement_set_max_sets),
    };

    try {
        await saveApi.run(() =>
            policyExists.value
                ? updatePromotionPolicy(body)
                : storePromotionPolicy(body),
        );
        toast.success("Политика сохранена");
        await loadPolicy();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function openRoleModal() {
    roleForm.product_id = "";
    roleForm.role = "roll";
    roleForm.name = "";
    roleForm.price_rubles = "";
    clearErrors(roleErrors);
    roleModalOpen.value = true;
}

function validateRole() {
    clearErrors(roleErrors);
    if (!String(roleForm.product_id).trim()) {
        roleErrors.product_id = "Обязательное поле";
    }
    if (!roleForm.role) {
        roleErrors.role = "Обязательное поле";
    }
    if (!String(roleForm.name).trim()) {
        roleErrors.name = "Обязательное поле";
    }
    if (roleForm.price_rubles === "" || Number.isNaN(Number(roleForm.price_rubles))) {
        roleErrors.price_rubles = "Укажите цену";
    }
    return Object.keys(roleErrors).length === 0;
}

async function saveRole() {
    if (!validateRole()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const body = {
        product_id: Number(roleForm.product_id),
        role: roleForm.role,
        name: roleForm.name.trim(),
        price_kopecks: Math.round(Number(roleForm.price_rubles) * 100),
    };

    try {
        await saveApi.run(() => assignProductRole(body));
        toast.success("Роль назначена");
        roleModalOpen.value = false;
        await loadPolicy();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function onSyncZones() {
    try {
        const payload = await saveApi.run(() => syncZones());
        if (payload?.zones) {
            zones.value = payload.zones;
        } else {
            await loadPolicy();
        }
        toast.success("Зоны синхронизированы");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка синхронизации");
    }
}

function roleVariant(role) {
    if (role === "gift") {
        return "success";
    }
    if (role === "complement") {
        return "warning";
    }
    return "neutral";
}

onMounted(loadPolicy);
</script>

<template>
    <div class="space-y-4 p-4 md:p-6">
        <UiCard title="Promotion">
            <div class="mb-4 flex flex-wrap gap-2">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    type="button"
                    class="rounded px-3 py-1.5 text-sm"
                    :class="tab === item.id
                        ? 'bg-slate-900 text-white'
                        : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    @click="tab = item.id"
                >
                    {{ item.label }}
                </button>
            </div>

            <div v-if="tab === 'policy'" class="space-y-3">
                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField
                        label="Порог подарка pickup (коп.)"
                        required
                        :error="policyErrors.gift_pickup_min_order_kopecks"
                    >
                        <UiInput v-model="policyForm.gift_pickup_min_order_kopecks" type="number" />
                    </UiFormField>
                    <UiFormField
                        label="Порог подарка courier (коп.)"
                        required
                        :error="policyErrors.gift_courier_min_order_kopecks"
                    >
                        <UiInput v-model="policyForm.gift_courier_min_order_kopecks" type="number" />
                    </UiFormField>
                </div>

                <UiFormField label="Дни недели (courier)">
                    <div class="mt-2 flex flex-wrap gap-3">
                        <label
                            v-for="day in weekdayOptions"
                            :key="day.value"
                            class="flex items-center gap-1 text-sm"
                        >
                            <input
                                type="checkbox"
                                :checked="policyForm.gift_courier_weekdays.includes(day.value)"
                                @change="toggleWeekday(day.value)"
                            >
                            {{ day.label }}
                        </label>
                    </div>
                </UiFormField>

                <div class="flex flex-wrap gap-4 text-sm">
                    <label class="flex items-center gap-2">
                        <input v-model="policyForm.gift_benefit_active" type="checkbox">
                        gift_benefit_active
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="policyForm.delivery_benefit_active" type="checkbox">
                        delivery_benefit_active
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="policyForm.complement_set_benefit_active" type="checkbox">
                        complement_set_benefit_active
                    </label>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField
                        label="rolls_per_set"
                        required
                        :error="policyErrors.complement_set_rolls_per_set"
                    >
                        <UiInput v-model="policyForm.complement_set_rolls_per_set" type="number" />
                    </UiFormField>
                    <UiFormField
                        label="max_sets"
                        required
                        :error="policyErrors.complement_set_max_sets"
                    >
                        <UiInput v-model="policyForm.complement_set_max_sets" type="number" />
                    </UiFormField>
                    <UiFormField
                        label="Порог бесплатной доставки (коп.)"
                        required
                        :error="policyErrors.delivery_free_threshold_kopecks"
                    >
                        <UiInput v-model="policyForm.delivery_free_threshold_kopecks" type="number" />
                    </UiFormField>
                    <UiFormField
                        label="Надбавка вне зоны (коп.)"
                        required
                        :error="policyErrors.delivery_outside_zone_surcharge_kopecks"
                    >
                        <UiInput
                            v-model="policyForm.delivery_outside_zone_surcharge_kopecks"
                            type="number"
                        />
                    </UiFormField>
                </div>

                <UiButton :disabled="saveApi.loading" @click="savePolicy">
                    Сохранить
                </UiButton>
            </div>

            <div v-else-if="tab === 'roles'" class="space-y-3">
                <div class="flex justify-end">
                    <UiButton @click="openRoleModal">+ Роль</UiButton>
                </div>
                <UiTable
                    :columns="roleColumns"
                    :rows="roleRows"
                    :loading="loadApi.loading"
                    empty-text="Нет ролей"
                >
                    <template #cell-role="{ value }">
                        <UiBadge :variant="roleVariant(value)" :text="String(value)" />
                    </template>
                </UiTable>
            </div>

            <div v-else class="space-y-3">
                <div class="flex justify-end">
                    <UiButton :disabled="saveApi.loading" @click="onSyncZones">
                        Синхронизировать зоны
                    </UiButton>
                </div>
                <UiTable
                    :columns="zoneColumns"
                    :rows="zoneRows"
                    :loading="loadApi.loading"
                    empty-text="Нет зон"
                >
                    <template #cell-is_remote="{ value }">
                        <UiBadge
                            :variant="value ? 'warning' : 'neutral'"
                            :text="value ? 'да' : 'нет'"
                        />
                    </template>
                </UiTable>
            </div>
        </UiCard>

        <UiModal :open="roleModalOpen" title="Назначить роль" @close="roleModalOpen = false">
            <div class="space-y-3">
                <UiFormField label="product_id" required :error="roleErrors.product_id">
                    <UiInput v-model="roleForm.product_id" type="number" />
                </UiFormField>
                <UiFormField label="Роль" required :error="roleErrors.role">
                    <select
                        v-model="roleForm.role"
                        class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2"
                    >
                        <option value="roll">roll</option>
                        <option value="gift">gift</option>
                        <option value="complement">complement</option>
                    </select>
                </UiFormField>
                <UiFormField label="Название" required :error="roleErrors.name">
                    <UiInput v-model="roleForm.name" />
                </UiFormField>
                <UiFormField label="Цена (₽)" required :error="roleErrors.price_rubles">
                    <UiInput v-model="roleForm.price_rubles" type="number" />
                </UiFormField>
            </div>
            <template #footer>
                <UiButton @click="roleModalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="saveRole">Сохранить</UiButton>
            </template>
        </UiModal>
    </div>
</template>
