<script setup>
import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { backofficeForm, backofficeRequest } from "../../api";
import { catalogStatusLabel, useCatalog } from "../../catalog";

const tabs = [
    { id: "categories", label: "Категории" },
    { id: "products", label: "Товары" },
    { id: "sets", label: "Наборы" },
    { id: "tags", label: "Теги" },
];

const tab = ref("categories");
const draft = ref(null);
const images = ref([]);
const imageFile = ref(null);
const error = ref("");
const catalog = useCatalog();
const { categories, products, sets, tags, catalogReady } = storeToRefs(catalog);

const rows = computed(() => {
    if (tab.value === "products") return products.value;
    if (tab.value === "sets") return sets.value;
    if (tab.value === "tags") return tags.value;
    return categories.value;
});

watch(tab, () => {
    draft.value = null;
    images.value = [];
});

function blank() {
    if (tab.value === "categories") {
        return { id: null, name: "", isActive: true, isAccompanying: false };
    }
    if (tab.value === "tags") {
        return { id: null, code: "", label: "", color: "", isActive: true };
    }
    if (tab.value === "sets") {
        return { id: null, name: "", sku: "", price: 0, oldPrice: null, status: "active" };
    }
    return { id: null, name: "", sku: "", price: 0, oldPrice: null, weight: null, status: "active", description: "" };
}

async function edit(row) {
    draft.value = { ...row };
    images.value = [];
    if (tab.value !== "products" || !row.id) {
        return;
    }
    try {
        const payload = await backofficeRequest("GET", `/api/backoffice/catalog/products/${row.id}/images`);
        images.value = payload.images ?? [];
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

async function submit() {
    error.value = "";
    const current = draft.value;
    try {
        if (tab.value === "categories") {
            const body = { name: current.name, is_active: current.isActive, is_accompanying: current.isAccompanying };
            const path = current.id ? `/api/backoffice/catalog/categories/${current.id}` : "/api/backoffice/catalog/categories";
            await backofficeRequest(current.id ? "PUT" : "POST", path, body);
        } else if (tab.value === "tags") {
            const body = { code: current.code, label: current.label, color: current.color, is_active: current.isActive };
            const path = current.id ? `/api/backoffice/catalog/tags/${current.id}` : "/api/backoffice/catalog/tags";
            await backofficeRequest(current.id ? "PUT" : "POST", path, body);
        } else if (tab.value === "sets") {
            const body = { name: current.name, sku: current.sku, price: current.price, old_price: current.oldPrice, status: current.status };
            const path = current.id ? `/api/backoffice/catalog/sets/${current.id}` : "/api/backoffice/catalog/sets";
            await backofficeRequest(current.id ? "PUT" : "POST", path, body);
        } else {
            const body = {
                name: current.name,
                sku: current.sku,
                price: current.price,
                old_price: current.oldPrice,
                weight: current.weight,
                status: current.status,
                description: current.description,
            };
            const path = current.id ? `/api/backoffice/catalog/products/${current.id}` : "/api/backoffice/catalog/products";
            const payload = await backofficeRequest(current.id ? "PUT" : "POST", path, body);
            draft.value = { ...current, id: payload.id };
        }
        await catalog.loadCatalog();
        if (tab.value !== "products") {
            draft.value = null;
        }
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

async function uploadImage() {
    if (!draft.value?.id || !imageFile.value) {
        return;
    }
    error.value = "";
    const body = new FormData();
    body.append("image", imageFile.value);
    try {
        const payload = await backofficeForm(`/api/backoffice/catalog/products/${draft.value.id}/images`, body);
        images.value = payload.images ?? [];
        imageFile.value = null;
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Каталог</h1>
        <div class="flex flex-wrap gap-1">
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                class="inline-flex h-9 items-center rounded px-3 text-sm"
                :class="tab === item.id ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                @click="tab = item.id"
            >
                {{ item.label }}
            </button>
        </div>
        <p
            v-if="error"
            class="text-sm text-red-700"
        >
            {{ error }}
        </p>
        <p
            v-if="!catalogReady"
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
        <div
            v-else
            class="overflow-x-auto rounded border border-neutral-200 bg-white"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Название</th>
                        <th class="px-3 py-2 font-medium">{{ tab === "tags" ? "Код" : "Цена / статус" }}</th>
                        <th class="px-3 py-2 font-medium" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in rows"
                        :key="row.id"
                        class="border-b border-neutral-100 last:border-0"
                    >
                        <td class="px-3 py-2">{{ row.name || row.label }}</td>
                        <td class="px-3 py-2">{{ row.code || (row.price != null ? `${row.price} ₽` : catalogStatusLabel(row.isActive ? "active" : "archived")) }}</td>
                        <td class="px-3 py-2">
                            <button
                                type="button"
                                class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                                @click="edit(row)"
                            >
                                Править
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button
            type="button"
            class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
            @click="draft = blank()"
        >
            Новый
        </button>
        <form
            v-if="draft"
            class="grid gap-3 sm:grid-cols-2"
            @submit.prevent="submit"
        >
            <template v-if="tab === 'categories'">
                <input
                    v-model="draft.name"
                    required
                    placeholder="Название"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm sm:col-span-2"
                >
                <label class="flex items-center gap-2 text-sm"><input
                    v-model="draft.isActive"
                    type="checkbox"
                > Активна</label>
                <label class="flex items-center gap-2 text-sm"><input
                    v-model="draft.isAccompanying"
                    type="checkbox"
                > Сопроводительная</label>
            </template>
            <template v-else-if="tab === 'tags'">
                <input
                    v-model="draft.code"
                    required
                    placeholder="Код"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <input
                    v-model="draft.label"
                    required
                    placeholder="Подпись"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <input
                    v-model="draft.color"
                    placeholder="Цвет"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <label class="flex items-center gap-2 text-sm"><input
                    v-model="draft.isActive"
                    type="checkbox"
                > Активен</label>
            </template>
            <template v-else>
                <input
                    v-model="draft.name"
                    required
                    placeholder="Название"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <input
                    v-model="draft.sku"
                    placeholder="Артикул"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <input
                    v-model.number="draft.price"
                    type="number"
                    min="0"
                    required
                    placeholder="Цена, ₽"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <input
                    v-model.number="draft.oldPrice"
                    type="number"
                    min="0"
                    placeholder="Старая цена, ₽"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <select
                    v-model="draft.status"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                    <option value="active">Активен</option>
                    <option value="archived">В архиве</option>
                </select>
                <input
                    v-if="tab === 'products'"
                    v-model.number="draft.weight"
                    type="number"
                    min="0"
                    placeholder="Вес, г"
                    class="h-9 rounded border border-neutral-300 px-3 text-sm"
                >
                <textarea
                    v-if="tab === 'products'"
                    v-model="draft.description"
                    rows="3"
                    placeholder="Описание"
                    class="rounded border border-neutral-300 px-3 py-2 text-sm sm:col-span-2"
                />
            </template>
            <div
                v-if="tab === 'products' && draft.id"
                class="space-y-2 sm:col-span-2"
            >
                <p class="text-sm">Картинки</p>
                <div class="flex flex-wrap gap-2">
                    <img
                        v-for="image in images"
                        :key="image.id"
                        :src="image.url"
                        alt=""
                        class="h-16 w-16 rounded border border-neutral-200 object-cover"
                    >
                </div>
                <p
                    v-if="images.length"
                    class="text-xs text-neutral-500"
                >
                    Фокус последней: {{ images.at(-1).focus_x ?? "—" }} / {{ images.at(-1).focus_y ?? "—" }}
                </p>
                <input
                    type="file"
                    accept="image/*"
                    class="text-sm"
                    @change="imageFile = $event.target.files?.[0] ?? null"
                >
                <button
                    type="button"
                    class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                    @click="uploadImage"
                >
                    Загрузить картинку
                </button>
            </div>
            <button
                type="submit"
                class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
            >
                Сохранить
            </button>
        </form>
    </section>
</template>
