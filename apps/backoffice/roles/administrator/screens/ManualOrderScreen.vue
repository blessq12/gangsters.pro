<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { getCatalog } from "../../../api/catalog.js";
import { storeOrder } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";

const router = useRouter();
const toast = useToast();
const catalogApi = useApi();
const saveApi = useApi();

const products = ref([]);
const fieldErrors = reactive({});

const form = reactive({
    name: "",
    phone: "",
    email: "",
    method: "pickup",
    street: "",
    house: "",
    entrance: "",
    floor: "",
    apartment: "",
    comment: "",
    payment_method: "cash",
    change_from: "",
    lines: [{ product_id: "", qty: "1" }],
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

async function loadCatalog() {
    try {
        const payload = await catalogApi.run(() => getCatalog());
        products.value = payload?.products ?? [];
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

function addLine() {
    form.lines.push({ product_id: "", qty: "1" });
}

function removeLine(index) {
    if (form.lines.length <= 1) {
        return;
    }
    form.lines.splice(index, 1);
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    if (!String(form.phone).trim()) {
        fieldErrors.phone = "Обязательное поле";
    }
    if (form.method === "courier" && !String(form.street).trim()) {
        fieldErrors.street = "Нужна улица";
    }
    if (form.method === "courier" && !String(form.house).trim()) {
        fieldErrors.house = "Нужен дом";
    }
    if (!form.payment_method) {
        fieldErrors.payment_method = "Обязательное поле";
    }
    const validLines = form.lines.filter(
        (line) => line.product_id && Number(line.qty) > 0,
    );
    if (validLines.length === 0) {
        fieldErrors.lines = "Добавь хотя бы одну позицию";
    }
    return Object.keys(fieldErrors).length === 0;
}

function checkoutBody() {
    const lines = form.lines
        .filter((line) => line.product_id && Number(line.qty) > 0)
        .map((line) => ({
            product_id: Number(line.product_id),
            quantity: Number(line.qty),
        }));

    return {
        client_request_id: `admin-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        lines,
        delivery_method: form.method,
        address: form.method === "courier"
            ? {
                street: form.street.trim(),
                house: form.house.trim(),
                entrance: form.entrance.trim() || null,
                floor: form.floor.trim() || null,
                apartment: form.apartment.trim() || null,
                comment: form.comment.trim() || null,
            }
            : null,
        client: {
            kind: "guest",
            name: form.name.trim(),
            phone: form.phone.trim(),
            email: form.email.trim() || null,
        },
        payment_method: form.payment_method,
        change_from_rubles: form.change_from === "" ? null : Number(form.change_from),
    };
}

async function createOrder() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const body = checkoutBody();

    try {
        await saveApi.run(() => storeOrder(body));
        toast.success("Заказ создан");
        router.push({ name: "administrator-live-orders" });
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка создания");
    }
}

onMounted(loadCatalog);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Ручной заказ">
            <div class="space-y-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField label="Имя" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                    <UiFormField label="Телефон" required :error="fieldErrors.phone">
                        <UiInput v-model="form.phone" type="tel" />
                    </UiFormField>
                    <UiFormField label="Email">
                        <UiInput v-model="form.email" type="email" />
                    </UiFormField>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField label="Доставка" required>
                        <select
                            v-model="form.method"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        >
                            <option value="pickup">pickup</option>
                            <option value="courier">courier</option>
                        </select>
                    </UiFormField>
                    <UiFormField
                        v-if="form.method === 'courier'"
                        label="Улица"
                        required
                        :error="fieldErrors.street"
                    >
                        <UiInput v-model="form.street" />
                    </UiFormField>
                    <UiFormField
                        v-if="form.method === 'courier'"
                        label="Дом"
                        required
                        :error="fieldErrors.house"
                    >
                        <UiInput v-model="form.house" />
                    </UiFormField>
                    <UiFormField v-if="form.method === 'courier'" label="Подъезд">
                        <UiInput v-model="form.entrance" />
                    </UiFormField>
                    <UiFormField v-if="form.method === 'courier'" label="Этаж">
                        <UiInput v-model="form.floor" />
                    </UiFormField>
                    <UiFormField v-if="form.method === 'courier'" label="Квартира">
                        <UiInput v-model="form.apartment" />
                    </UiFormField>
                    <UiFormField v-if="form.method === 'courier'" label="Комментарий">
                        <UiInput v-model="form.comment" />
                    </UiFormField>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField
                        label="Оплата"
                        required
                        :error="fieldErrors.payment_method"
                    >
                        <select
                            v-model="form.payment_method"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        >
                            <option value="cash">cash</option>
                            <option value="card_courier">card_courier</option>
                            <option value="card_online">card_online</option>
                        </select>
                    </UiFormField>
                    <UiFormField label="Сдача с">
                        <UiInput v-model="form.change_from" type="number" />
                    </UiFormField>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-bo-fg">Позиции</p>
                        <UiButton @click="addLine">+ Строка</UiButton>
                    </div>
                    <p v-if="fieldErrors.lines" class="text-xs text-red-300">
                        {{ fieldErrors.lines }}
                    </p>
                    <div
                        v-for="(line, index) in form.lines"
                        :key="index"
                        class="grid gap-2 md:grid-cols-[1fr_120px_auto]"
                    >
                        <select
                            v-model="line.product_id"
                            class="border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none text-sm"
                        >
                            <option value="">Товар</option>
                            <option
                                v-for="product in products"
                                :key="product.id"
                                :value="String(product.id)"
                            >
                                {{ product.id }} — {{ product.name }} ({{ product.price }} ₽)
                            </option>
                        </select>
                        <UiInput v-model="line.qty" type="number" />
                        <UiButton @click="removeLine(index)">Удалить</UiButton>
                    </div>
                </div>

                <UiButton :disabled="saveApi.loading" @click="createOrder">
                    Создать заказ
                </UiButton>
            </div>
        </UiCard>
    </div>
</template>
