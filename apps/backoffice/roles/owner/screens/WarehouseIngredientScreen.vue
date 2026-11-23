<script setup>
import { computed, onMounted, reactive, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getIngredient,
    storeIngredient,
    updateIngredient,
} from "../../../api/warehouse.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const isNew = computed(() => route.params.id === "new");
const ingredientId = computed(() => (isNew.value ? null : Number(route.params.id)));

const fieldErrors = reactive({});
const form = reactive({
    name: "",
    unit: "",
});

const title = computed(() => {
    if (isNew.value) {
        return "Новый ингредиент";
    }
    const name = String(form.name ?? "").trim();
    return name !== "" ? name : "Ингредиент";
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function applyDetail(payload) {
    form.name = payload?.name ?? "";
    form.unit = payload?.unit ?? "";
    clearErrors();
}

async function load() {
    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getIngredient(ingredientId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить");
        router.push({ name: "owner-warehouse" });
    }
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    if (!String(form.unit).trim()) {
        fieldErrors.unit = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    const body = {
        name: form.name.trim(),
        unit: form.unit.trim(),
    };
    try {
        const result = await saveApi.run(() => (
            ingredientId.value
                ? updateIngredient(ingredientId.value, body)
                : storeIngredient(body)
        ));
        toast.success(ingredientId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({
                name: "owner-warehouse-ingredient",
                params: { id: result.id },
            });
            return;
        }
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
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
                    @click="router.push({ name: 'owner-warehouse' })"
                >
                    К складу
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
            </div>
            <UiButton :disabled="saveApi.loading || loadApi.loading" @click="save">
                Сохранить
            </UiButton>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </p>

        <UiCard v-else title="Карточка">
            <div class="grid gap-4 sm:grid-cols-2">
                <UiFormField label="Название" required :error="fieldErrors.name">
                    <UiInput v-model="form.name" />
                </UiFormField>
                <UiFormField label="Единица" required :error="fieldErrors.unit">
                    <UiInput v-model="form.unit" placeholder="г, кг, шт…" />
                </UiFormField>
            </div>
        </UiCard>
    </div>
</template>
