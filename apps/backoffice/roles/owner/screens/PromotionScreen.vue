<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import { listProducts } from "../../../api/catalog.js";
import {
    getPromotionPolicy,
    storePromotionPolicy,
    updatePromotionPolicy,
} from "../../../api/promotion.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import OwnerBranchSelector from "../components/branch/OwnerBranchSelector.vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const policyExists = ref(false);
const catalogProducts = ref([]);
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();

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
    gift_product_id: "",
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

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

function applyPolicy(payload) {
    policyExists.value = Boolean(payload?.gift || payload?.delivery || payload?.complement);
    if (!payload) {
        policyForm.gift_product_id = "";
        policyForm.gift_pickup_min_order_kopecks = "0";
        policyForm.gift_courier_min_order_kopecks = "0";
        policyForm.gift_courier_weekdays = [];
        policyForm.gift_benefit_active = false;
        policyForm.delivery_free_threshold_kopecks = "0";
        policyForm.delivery_outside_zone_surcharge_kopecks = "0";
        policyForm.delivery_benefit_active = false;
        policyForm.complement_set_benefit_active = false;
        policyForm.complement_set_rolls_per_set = "1";
        policyForm.complement_set_max_sets = "1";
        return;
    }
    policyForm.gift_product_id = payload?.gift?.product_id != null
        ? String(payload.gift.product_id)
        : "";
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
}

async function loadPolicy() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        applyPolicy(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getPromotionPolicy(branchId));
        if (branchId === selectedBranchId.value) {
            applyPolicy(payload);
        }
    } catch (err) {
        if (err?.status === 404) {
            applyPolicy(null);
            return;
        }
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
    if (!selectedBranchId.value) {
        toast.error("Выбери филиал");
        return;
    }
    if (!validatePolicy()) {
        toast.error("Проверьте поля");
        return;
    }

    const giftProductId = String(policyForm.gift_product_id).trim();
    const body = {
        branch_id: Number(selectedBranchId.value),
        gift_product_id: giftProductId !== "" ? Number(giftProductId) : null,
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

async function loadCatalogProducts() {
    try {
        const payload = await listProducts({ limit: 200 });
        catalogProducts.value = Array.isArray(payload?.items) ? payload.items : [];
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

watch(selectedBranchId, () => {
    loadPolicy();
});
onMounted(async () => {
    await ensureLoaded();
    await Promise.all([loadPolicy(), loadCatalogProducts()]);
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Политики филиала">
            <template #actions>
                <OwnerBranchSelector />
            </template>

            <p v-if="!selectedBranchId" class="text-sm text-bo-muted">
                Выбери филиал, чтобы настроить правила и подарок.
            </p>

            <div v-else class="space-y-5">
                <p class="text-sm text-bo-muted">
                    Роллы и комплектные товары задаются флагами в карточке товара каталога.
                    Здесь — правила филиала и явный товар-подарок.
                </p>

                <UiFormField label="Товар-подарок филиала">
                    <select
                        v-model="policyForm.gift_product_id"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    >
                        <option value="">Без подарка</option>
                        <option
                            v-for="product in catalogProducts"
                            :key="product.id"
                            :value="String(product.id)"
                        >
                            {{ product.name }}
                        </option>
                    </select>
                </UiFormField>

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
                        Подарок активен
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="policyForm.delivery_benefit_active" type="checkbox">
                        Выгода доставки активна
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="policyForm.complement_set_benefit_active" type="checkbox">
                        Комплекты активны
                    </label>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField
                        label="Роллов на комплект"
                        required
                        :error="policyErrors.complement_set_rolls_per_set"
                    >
                        <UiInput v-model="policyForm.complement_set_rolls_per_set" type="number" />
                    </UiFormField>
                    <UiFormField
                        label="Макс. комплектов"
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
        </UiCard>
    </div>
</template>
