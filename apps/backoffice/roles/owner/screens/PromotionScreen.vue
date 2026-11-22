<script setup>
import { onMounted, reactive, ref, watch } from "vue";
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
import UiToggle from "../../../shell/components/ui/UiToggle.vue";
import OwnerBranchSelector from "../components/branch/OwnerBranchSelector.vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const policyExists = ref(false);
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
    gift_pickup_min_order_rubles: "0",
    gift_courier_min_order_rubles: "0",
    gift_courier_weekdays: /** @type {number[]} */ ([]),
    gift_benefit_active: false,
    delivery_free_threshold_rubles: "0",
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

/** @param {number|null|undefined} kopecks */
function kopecksToRubles(kopecks) {
    return String(Math.round(Number(kopecks ?? 0) / 100));
}

/** @param {string|number} value @returns {number|null} */
function rublesToKopecks(value) {
    const text = String(value ?? "").trim();
    if (text === "" || Number.isNaN(Number(text))) {
        return null;
    }
    return Math.round(Number(text) * 100);
}

function applyPolicy(payload) {
    policyExists.value = Boolean(payload?.gift || payload?.delivery || payload?.complement);
    if (!payload) {
        policyForm.gift_pickup_min_order_rubles = "0";
        policyForm.gift_courier_min_order_rubles = "0";
        policyForm.gift_courier_weekdays = [];
        policyForm.gift_benefit_active = false;
        policyForm.delivery_free_threshold_rubles = "0";
        policyForm.delivery_benefit_active = false;
        policyForm.complement_set_benefit_active = false;
        policyForm.complement_set_rolls_per_set = "1";
        policyForm.complement_set_max_sets = "1";
        return;
    }
    policyForm.gift_pickup_min_order_rubles = kopecksToRubles(
        payload?.gift?.pickup_min_order_kopecks,
    );
    policyForm.gift_courier_min_order_rubles = kopecksToRubles(
        payload?.gift?.courier_min_order_kopecks,
    );
    policyForm.gift_courier_weekdays = Array.isArray(payload?.gift?.courier_weekdays)
        ? [...payload.gift.courier_weekdays]
        : [];
    policyForm.gift_benefit_active = Boolean(payload?.gift?.is_active);
    policyForm.delivery_free_threshold_rubles = kopecksToRubles(
        payload?.delivery?.free_delivery_threshold_kopecks,
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
    const moneyKeys = [
        "gift_pickup_min_order_rubles",
        "gift_courier_min_order_rubles",
        "delivery_free_threshold_rubles",
    ];
    moneyKeys.forEach((key) => {
        if (rublesToKopecks(policyForm[key]) === null) {
            policyErrors[key] = "Число";
        }
    });
    ["complement_set_rolls_per_set", "complement_set_max_sets"].forEach((key) => {
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

    const body = {
        branch_id: Number(selectedBranchId.value),
        gift_pickup_min_order_kopecks: rublesToKopecks(policyForm.gift_pickup_min_order_rubles),
        gift_courier_min_order_kopecks: rublesToKopecks(policyForm.gift_courier_min_order_rubles),
        gift_courier_weekdays: [...policyForm.gift_courier_weekdays],
        gift_benefit_active: Boolean(policyForm.gift_benefit_active),
        delivery_free_threshold_kopecks: rublesToKopecks(policyForm.delivery_free_threshold_rubles),
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
        toast.success("Сохранено");
        await loadPolicy();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

watch(selectedBranchId, loadPolicy);
onMounted(async () => {
    await ensureLoaded();
    await loadPolicy();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">
                    Акции
                </h1>
                <UiButton
                    :disabled="!selectedBranchId || saveApi.loading || loadApi.loading"
                    @click="savePolicy"
                >
                    Сохранить
                </UiButton>
            </div>
            <OwnerBranchSelector />
        </div>

        <p v-if="!selectedBranchId" class="text-sm text-bo-muted">
            Выбери филиал, чтобы настроить правила акций.
        </p>

        <template v-else>
            <p v-if="loadApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </p>

            <template v-else>
                <UiCard title="Подарок">
                    <template #actions>
                        <UiToggle
                            v-model="policyForm.gift_benefit_active"
                            label-on="Активен"
                            label-off="Выключен"
                        />
                    </template>
                    <p class="mb-4 text-sm text-bo-muted">
                        Товар-подарок задаётся флагом в каталоге филиала. Здесь — только пороги и дни.
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <UiFormField
                            label="Порог pickup (₽)"
                            required
                            :error="policyErrors.gift_pickup_min_order_rubles"
                        >
                            <UiInput v-model="policyForm.gift_pickup_min_order_rubles" type="number" min="0" step="1" />
                        </UiFormField>
                        <UiFormField
                            label="Порог courier (₽)"
                            required
                            :error="policyErrors.gift_courier_min_order_rubles"
                        >
                            <UiInput v-model="policyForm.gift_courier_min_order_rubles" type="number" min="0" step="1" />
                        </UiFormField>
                    </div>
                    <UiFormField class="mt-4" label="Дни недели (courier)">
                        <div class="mt-2 flex flex-wrap gap-3">
                            <label
                                v-for="day in weekdayOptions"
                                :key="day.value"
                                class="flex items-center gap-1 text-sm text-bo-fg"
                            >
                                <input
                                    type="checkbox"
                                    class="accent-bo-accent"
                                    :checked="policyForm.gift_courier_weekdays.includes(day.value)"
                                    @change="toggleWeekday(day.value)"
                                >
                                {{ day.label }}
                            </label>
                        </div>
                    </UiFormField>
                </UiCard>

                <UiCard title="Бесплатная доставка">
                    <template #actions>
                        <UiToggle
                            v-model="policyForm.delivery_benefit_active"
                            label-on="Активна"
                            label-off="Выключена"
                        />
                    </template>
                    <UiFormField
                        label="Порог бесплатной доставки (₽)"
                        required
                        :error="policyErrors.delivery_free_threshold_rubles"
                    >
                        <UiInput v-model="policyForm.delivery_free_threshold_rubles" type="number" min="0" step="1" />
                    </UiFormField>
                </UiCard>

                <UiCard title="Комплекты">
                    <template #actions>
                        <UiToggle
                            v-model="policyForm.complement_set_benefit_active"
                            label-on="Активны"
                            label-off="Выключены"
                        />
                    </template>
                    <div class="grid gap-4 sm:grid-cols-2">
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
                    </div>
                </UiCard>
            </template>
        </template>
    </div>
</template>
