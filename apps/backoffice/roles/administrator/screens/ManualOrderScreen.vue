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
    address: "",
    payment_method: "cash",
    change_from: "",
    delivery_fee: "0",
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

function productById(id) {
    return products.value.find((item) => String(item.id) === String(id)) ?? null;
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    if (!String(form.phone).trim()) {
        fieldErrors.phone = "Обязательное поле";
    }
    if (form.method === "courier" && !String(form.address).trim()) {
        fieldErrors.address = "Нужен адрес";
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

function buildSnapshot() {
    const lines = form.lines
        .filter((line) => line.product_id && Number(line.qty) > 0)
        .map((line) => {
            const product = productById(line.product_id);
            const qty = Number(line.qty);
            const price = Number(product?.price ?? 0);
            return {
                product_id: Number(line.product_id),
                product_name: product?.name ?? String(line.product_id),
                quantity: qty,
                unit_price_rubles: price,
                line_total_rubles: price * qty,
            };
        });

    const itemsTotal = lines.reduce((sum, line) => sum + Number(line.line_total_rubles), 0);
    const deliveryFee = form.method === "courier" ? Number(form.delivery_fee || 0) : 0;
    const totals = {
        items_total_rubles: itemsTotal,
        delivery_fee_rubles: deliveryFee,
        grand_total_rubles: itemsTotal + deliveryFee,
    };

    return {
        cart: {
            lines,
            totals,
        },
        client: {
            name: form.name.trim(),
            phone: form.phone.trim(),
            email: form.email.trim() || null,
            kind: "guest",
        },
        delivery: {
            method: form.method,
            address: form.method === "courier"
                ? { line: form.address.trim() }
                : null,
        },
        payment: {
            method: form.payment_method,
            change_from: form.change_from === "" ? null : Number(form.change_from),
        },
        totals,
    };
}

async function createOrder() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const body = {
        client_request_id: `admin-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        quote_snapshot: buildSnapshot(),
    };

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
    <div class="space-y-4 p-4 md:p-6">
        <UiCard title="Ручной заказ">
            <div class="space-y-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField label="Имя" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                    <UiFormField label="Телефон" required :error="fieldErrors.phone">
                        <UiInput v-model="form.phone" />
                    </UiFormField>
                    <UiFormField label="Email">
                        <UiInput v-model="form.email" type="email" />
                    </UiFormField>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <UiFormField label="Доставка" required>
                        <select
                            v-model="form.method"
                            class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2"
                        >
                            <option value="pickup">pickup</option>
                            <option value="courier">courier</option>
                        </select>
                    </UiFormField>
                    <UiFormField
                        v-if="form.method === 'courier'"
                        label="Адрес"
                        required
                        :error="fieldErrors.address"
                    >
                        <UiInput v-model="form.address" />
                    </UiFormField>
                    <UiFormField
                        v-if="form.method === 'courier'"
                        label="Доставка (₽)"
                    >
                        <UiInput v-model="form.delivery_fee" type="number" />
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
                            class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2"
                        >
                            <option value="cash">cash</option>
                            <option value="card">card</option>
                            <option value="online">online</option>
                        </select>
                    </UiFormField>
                    <UiFormField label="Сдача с">
                        <UiInput v-model="form.change_from" type="number" />
                    </UiFormField>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-700">Позиции</p>
                        <UiButton @click="addLine">+ Строка</UiButton>
                    </div>
                    <p v-if="fieldErrors.lines" class="text-xs text-rose-600">
                        {{ fieldErrors.lines }}
                    </p>
                    <div
                        v-for="(line, index) in form.lines"
                        :key="index"
                        class="grid gap-2 md:grid-cols-[1fr_120px_auto]"
                    >
                        <select
                            v-model="line.product_id"
                            class="rounded border border-slate-300 bg-white px-3 py-2 text-sm"
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
