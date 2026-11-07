<script setup>
import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import Pager from "../../Pager.vue";
import { catalogStatusLabel, useCatalog } from "../../catalog";

const tabs = [
    { id: "categories", label: "Категории" },
    { id: "products", label: "Товары" },
    { id: "sets", label: "Наборы" },
    { id: "tags", label: "Теги" },
];

const tab = ref("categories");
const page = ref(1);
const perPage = 20;
const openCategoryIds = ref(new Set());
const openProductIds = ref(new Set());

const catalog = useCatalog();
const { categories, products, sets, tags, catalogError, catalogReady } = storeToRefs(catalog);

const pageSource = computed(() => {
    if (tab.value === "products") {
        return products.value;
    }
    if (tab.value === "sets") {
        return sets.value;
    }
    if (tab.value === "tags") {
        return tags.value;
    }
    return categories.value;
});

const pagedCategories = computed(() => slicePage(categories.value));
const pagedProducts = computed(() => slicePage(products.value));
const pagedSets = computed(() => slicePage(sets.value));
const pagedTags = computed(() => slicePage(tags.value));

function slicePage(list) {
    const start = (page.value - 1) * perPage;
    return list.slice(start, start + perPage);
}

watch(tab, () => {
    page.value = 1;
});

function isCategoryOpen(id) {
    return openCategoryIds.value.has(id);
}

function toggleCategory(id) {
    const next = new Set(openCategoryIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    openCategoryIds.value = next;
}

function isProductOpen(id) {
    return openProductIds.value.has(id);
}

function toggleProduct(id) {
    const next = new Set(openProductIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    openProductIds.value = next;
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Каталог</h1>
        <p
            v-if="catalogError"
            class="text-sm text-red-700"
        >
            {{ catalogError }}
        </p>
        <p
            v-else-if="!catalogReady"
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
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

        <div
            v-if="tab === 'categories'"
            class="overflow-x-auto rounded border border-neutral-200 bg-white"
        >
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Название</th>
                        <th class="px-3 py-2 font-medium">Позиций</th>
                        <th class="px-3 py-2 font-medium">Активна</th>
                        <th class="px-3 py-2 font-medium">Сопутствующее</th>
                    </tr>
                </thead>
                <tbody>
                    <template
                        v-for="category in pagedCategories"
                        :key="category.id"
                    >
                        <tr
                            class="cursor-pointer border-b border-neutral-100 hover:bg-neutral-50"
                            :class="isCategoryOpen(category.id) ? 'bg-neutral-50' : ''"
                            @click="toggleCategory(category.id)"
                        >
                            <td class="px-3 py-2">
                                <button
                                    type="button"
                                    class="mr-2 text-neutral-500"
                                    :aria-expanded="isCategoryOpen(category.id)"
                                    @click.stop="toggleCategory(category.id)"
                                >
                                    {{ isCategoryOpen(category.id) ? "▾" : "▸" }}
                                </button>
                                {{ category.name }}
                            </td>
                            <td class="px-3 py-2">{{ catalog.productsInCategory(category.id).length }}</td>
                            <td class="px-3 py-2">{{ category.isActive ? "Да" : "Нет" }}</td>
                            <td class="px-3 py-2">{{ category.isAccompanying ? "Да" : "Нет" }}</td>
                        </tr>
                        <tr
                            v-if="isCategoryOpen(category.id)"
                            class="border-b border-neutral-100 bg-neutral-50"
                        >
                            <td
                                colspan="4"
                                class="px-3 py-3 text-sm"
                            >
                                <p class="text-xs text-neutral-500">Товары</p>
                                <p
                                    v-if="catalog.productsInCategory(category.id).length === 0"
                                    class="mt-1"
                                >
                                    В категории пусто.
                                </p>
                                <ul
                                    v-else
                                    class="mt-1 space-y-1"
                                >
                                    <li
                                        v-for="product in catalog.productsInCategory(category.id)"
                                        :key="product.id"
                                    >
                                        {{ product.name }} · {{ product.price }} ₽ · {{ catalogStatusLabel(product.status) }}
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div
            v-else-if="tab === 'products'"
            class="overflow-x-auto rounded border border-neutral-200 bg-white"
        >
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Название</th>
                        <th class="px-3 py-2 font-medium">Цена, ₽</th>
                        <th class="px-3 py-2 font-medium">Вес, г</th>
                        <th class="px-3 py-2 font-medium">Активен</th>
                        <th class="px-3 py-2 font-medium">Системный</th>
                    </tr>
                </thead>
                <tbody>
                    <template
                        v-for="product in pagedProducts"
                        :key="product.id"
                    >
                        <tr
                            class="cursor-pointer border-b border-neutral-100 hover:bg-neutral-50"
                            :class="isProductOpen(product.id) ? 'bg-neutral-50' : ''"
                            @click="toggleProduct(product.id)"
                        >
                            <td class="px-3 py-2">
                                <button
                                    type="button"
                                    class="mr-2 text-neutral-500"
                                    :aria-expanded="isProductOpen(product.id)"
                                    @click.stop="toggleProduct(product.id)"
                                >
                                    {{ isProductOpen(product.id) ? "▾" : "▸" }}
                                </button>
                                {{ product.name }}
                            </td>
                            <td class="px-3 py-2">{{ product.price }}</td>
                            <td class="px-3 py-2">{{ product.weight }}</td>
                            <td class="px-3 py-2">{{ catalogStatusLabel(product.status) }}</td>
                            <td class="px-3 py-2">{{ product.isSystem ? "Да" : "Нет" }}</td>
                        </tr>
                        <tr
                            v-if="isProductOpen(product.id)"
                            class="border-b border-neutral-100 bg-neutral-50"
                        >
                            <td
                                colspan="5"
                                class="px-3 py-3"
                            >
                                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs text-neutral-500">Описание</dt>
                                        <dd>{{ product.description }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-neutral-500">Цены</dt>
                                        <dd>{{ product.price }} ₽</dd>
                                        <dd
                                            v-if="product.oldPrice"
                                            class="text-neutral-500"
                                        >
                                            Было {{ product.oldPrice }} ₽
                                        </dd>
                                        <dd class="mt-1">{{ product.isSystem ? "Системный" : "Витринный" }}</dd>
                                    </div>
                                </dl>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div
            v-else-if="tab === 'sets'"
            class="overflow-x-auto rounded border border-neutral-200 bg-white"
        >
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Название</th>
                        <th class="px-3 py-2 font-medium">Слаг</th>
                        <th class="px-3 py-2 font-medium">SKU</th>
                        <th class="px-3 py-2 font-medium">Позиций в наборе</th>
                        <th class="px-3 py-2 font-medium">Цена, ₽</th>
                        <th class="px-3 py-2 font-medium">Старая цена, ₽</th>
                        <th class="px-3 py-2 font-medium">Активен</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="set in pagedSets"
                        :key="set.id"
                        class="border-b border-neutral-100 last:border-0"
                    >
                        <td class="px-3 py-2">{{ set.name }}</td>
                        <td class="px-3 py-2">{{ set.slug }}</td>
                        <td class="px-3 py-2">{{ set.sku }}</td>
                        <td class="px-3 py-2">{{ set.lineCount }}</td>
                        <td class="px-3 py-2">{{ set.price }}</td>
                        <td class="px-3 py-2">{{ set.oldPrice ?? "—" }}</td>
                        <td class="px-3 py-2">{{ catalogStatusLabel(set.status) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-else
            class="overflow-x-auto rounded border border-neutral-200 bg-white"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Код</th>
                        <th class="px-3 py-2 font-medium">Подпись</th>
                        <th class="px-3 py-2 font-medium">Цвет</th>
                        <th class="px-3 py-2 font-medium">Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="tag in pagedTags"
                        :key="tag.id"
                        class="border-b border-neutral-100 last:border-0"
                    >
                        <td class="px-3 py-2">{{ tag.code }}</td>
                        <td class="px-3 py-2">{{ tag.label }}</td>
                        <td class="px-3 py-2">{{ tag.color }}</td>
                        <td class="px-3 py-2">{{ tag.isActive ? "Активен" : "Неактивен" }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Pager
            v-if="catalogReady"
            :page="page"
            :total="pageSource.length"
            :per-page="perPage"
            @update:page="page = $event"
        />
    </section>
</template>
