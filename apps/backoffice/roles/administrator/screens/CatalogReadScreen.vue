<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { getProduct, listProducts } from "../../../api/catalog.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const catalogApi = useApi();
const detailApi = useApi();
const { page, perPage, total, items, offset, applyPayload } = usePagedList();

const searchQuery = ref("");
let searchTimer = /** @type {ReturnType<typeof setTimeout>|null} */ (null);

const modalOpen = ref(false);
const product = ref(/** @type {Record<string, unknown>|null} */ (null));
const imageIndex = ref(0);

const columns = [
    { key: "name", label: "Название" },
    { key: "sku", label: "SKU", width: "120px" },
    { key: "weight", label: "Вес, г", width: "90px", align: "right" },
    { key: "is_roll", label: "Ролл", width: "90px" },
    { key: "is_system", label: "Системный", width: "110px" },
    { key: "status", label: "Статус", width: "110px" },
];

const rows = computed(() =>
    items.value.map((item) => ({
        id: item.id,
        name: item.name,
        sku: item.sku || "—",
        weight: item.weight != null ? item.weight : "—",
        is_roll: Boolean(item.is_roll),
        is_system: Boolean(item.is_system),
        is_active: item.is_active ?? item.status === "active",
    })),
);

const modalTitle = computed(() => {
    const name = String(product.value?.name ?? "").trim();
    return name !== "" ? name : "Товар";
});

const isActiveDetail = computed(() => String(product.value?.status ?? "") === "active");

const nutrition = computed(() => {
    const row = product.value?.nutrition;
    return row && typeof row === "object" ? row : null;
});

const ingredients = computed(() => {
    return Array.isArray(product.value?.ingredients)
        ? product.value.ingredients.map((item) => String(item).trim()).filter(Boolean)
        : [];
});

const techLines = computed(() => {
    const lines = product.value?.tech_card?.lines;
    return Array.isArray(lines) ? lines : [];
});

const images = computed(() => {
    return Array.isArray(product.value?.images) ? product.value.images : [];
});

const currentImage = computed(() => {
    if (images.value.length === 0) {
        return null;
    }
    const index = Math.min(Math.max(0, imageIndex.value), images.value.length - 1);
    return images.value[index] ?? null;
});

const basisLabel = computed(() => {
    const basis = String(nutrition.value?.basis ?? "");
    if (basis === "per_portion") {
        return "на порцию";
    }
    if (basis === "per_100g") {
        return "на 100 г";
    }
    return basis || "—";
});

async function load() {
    try {
        const q = searchQuery.value.trim();
        const payload = await catalogApi.run(() =>
            listProducts({
                offset: offset.value,
                limit: perPage,
                q: q || undefined,
            }),
        );
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

function scheduleSearch() {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    searchTimer = setTimeout(() => {
        if (page.value !== 1) {
            page.value = 1;
            return;
        }
        load();
    }, 320);
}

/**
 * @param {{ id?: number|string }} row
 */
async function openProduct(row) {
    const id = Number(row?.id);
    if (!Number.isFinite(id) || id < 1) {
        return;
    }
    modalOpen.value = true;
    product.value = null;
    imageIndex.value = 0;
    try {
        product.value = await detailApi.run(() => getProduct(id));
        imageIndex.value = 0;
    } catch (err) {
        modalOpen.value = false;
        product.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить товар");
    }
}

function closeModal() {
    modalOpen.value = false;
    product.value = null;
    imageIndex.value = 0;
}

function prevImage() {
    if (images.value.length < 2) {
        return;
    }
    imageIndex.value = (imageIndex.value - 1 + images.value.length) % images.value.length;
}

function nextImage() {
    if (images.value.length < 2) {
        return;
    }
    imageIndex.value = (imageIndex.value + 1) % images.value.length;
}

/**
 * @param {number} index
 */
function goToImage(index) {
    if (index < 0 || index >= images.value.length) {
        return;
    }
    imageIndex.value = index;
}

watch(page, load);
watch(searchQuery, scheduleSearch);
onMounted(load);
onBeforeUnmount(() => {
    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Каталог (просмотр)">
            <div class="mb-4 max-w-md">
                <UiInput
                    v-model="searchQuery"
                    type="search"
                    placeholder="Поиск по названию или артикулу"
                />
            </div>

            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="catalogApi.loading"
                empty-text="Ничего не найдено"
                @row-click="openProduct"
            >
                <template #cell-is_roll="{ value }">
                    <UiBadge variant="neutral" :text="value ? 'да' : 'нет'" />
                </template>
                <template #cell-is_system="{ value }">
                    <UiBadge
                        :variant="value ? 'warning' : 'neutral'"
                        :text="value ? 'да' : 'нет'"
                    />
                </template>
                <template #cell-status="{ row }">
                    <UiBadge
                        :variant="row.is_active ? 'success' : 'neutral'"
                        :text="row.is_active ? 'активен' : 'архив'"
                    />
                </template>
            </UiTable>

            <div class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </UiCard>

        <UiModal :open="modalOpen" :title="modalTitle" size="xl" @close="closeModal">
            <p v-if="detailApi.loading && !product" class="text-sm text-bo-muted">Загрузка…</p>
            <div
                v-else-if="product"
                class="grid gap-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]"
            >
                <aside>
                    <p
                        v-if="images.length === 0"
                        class="border border-bo-border bg-bo-field px-4 py-16 text-center text-sm text-bo-muted"
                    >
                        Нет фото
                    </p>
                    <div v-else class="space-y-3">
                        <div class="relative overflow-hidden border border-bo-border bg-bo-field">
                            <img
                                v-if="currentImage?.url"
                                :src="currentImage.url"
                                :alt="`Фото ${imageIndex + 1}`"
                                class="aspect-square w-full object-cover"
                                :style="{
                                    objectPosition: `${Number(currentImage.focus_x ?? 50)}% ${Number(currentImage.focus_y ?? 50)}%`,
                                }"
                            />
                            <template v-if="images.length > 1">
                                <button
                                    type="button"
                                    class="absolute left-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center border border-bo-border bg-bo-elevated/90 text-lg text-bo-fg"
                                    aria-label="Предыдущее фото"
                                    @click.stop="prevImage"
                                >
                                    ‹
                                </button>
                                <button
                                    type="button"
                                    class="absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center border border-bo-border bg-bo-elevated/90 text-lg text-bo-fg"
                                    aria-label="Следующее фото"
                                    @click.stop="nextImage"
                                >
                                    ›
                                </button>
                                <p
                                    class="absolute bottom-2 right-2 border border-bo-border bg-bo-elevated/90 px-2 py-1 text-xs text-bo-muted"
                                >
                                    {{ imageIndex + 1 }} / {{ images.length }}
                                </p>
                            </template>
                        </div>
                        <div v-if="images.length > 1" class="flex flex-wrap gap-2">
                            <button
                                v-for="(image, index) in images"
                                :key="image.id ?? index"
                                type="button"
                                class="h-14 w-14 overflow-hidden border"
                                :class="
                                    index === imageIndex
                                        ? 'border-bo-accent'
                                        : 'border-bo-border opacity-70 hover:opacity-100'
                                "
                                @click="goToImage(index)"
                            >
                                <img
                                    v-if="image.url"
                                    :src="image.url"
                                    :alt="`Миниатюра ${index + 1}`"
                                    class="h-full w-full object-cover"
                                    :style="{
                                        objectPosition: `${Number(image.focus_x ?? 50)}% ${Number(image.focus_y ?? 50)}%`,
                                    }"
                                />
                            </button>
                        </div>
                    </div>
                </aside>

                <div class="space-y-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <UiBadge
                            :variant="isActiveDetail ? 'success' : 'neutral'"
                            :text="isActiveDetail ? 'активен' : 'в архиве'"
                        />
                        <UiBadge v-if="product.is_roll" variant="neutral" text="ролл" />
                        <UiBadge v-if="product.is_system" variant="warning" text="системный" />
                    </div>

                    <dl class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-bo-muted">SKU</dt>
                            <dd class="mt-1 text-sm text-bo-fg">{{ product.sku || "—" }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-bo-muted">Вес (г)</dt>
                            <dd class="mt-1 text-sm text-bo-fg">{{ product.weight ?? "—" }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs uppercase tracking-wide text-bo-muted">Описание</dt>
                            <dd class="mt-1 whitespace-pre-wrap text-sm text-bo-fg">
                                {{ product.description || "—" }}
                            </dd>
                        </div>
                    </dl>

                    <div>
                        <h4 class="mb-2 text-sm font-medium text-bo-fg">Состав</h4>
                        <p v-if="ingredients.length === 0" class="text-sm text-bo-muted">
                            Не указан
                        </p>
                        <ul v-else class="flex flex-wrap gap-2">
                            <li
                                v-for="item in ingredients"
                                :key="item"
                                class="border border-bo-border bg-bo-field px-3 py-1.5 text-sm text-bo-fg"
                            >
                                {{ item }}
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-2 text-sm font-medium text-bo-fg">Техкарта</h4>
                        <p v-if="techLines.length === 0" class="text-sm text-bo-muted">Пусто</p>
                        <ul v-else class="divide-y divide-bo-border border border-bo-border">
                            <li
                                v-for="(row, index) in techLines"
                                :key="`${row.ingredient_ref}-${index}`"
                                class="flex flex-wrap items-center justify-between gap-2 px-3 py-2.5 text-sm text-bo-fg"
                            >
                                <span>{{ row.ingredient_ref || "—" }}</span>
                                <span class="text-bo-muted">{{ row.quantity }} {{ row.unit }}</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-2 text-sm font-medium text-bo-fg">Пищевая ценность</h4>
                        <dl class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-bo-muted">База</dt>
                                <dd class="mt-1 text-sm text-bo-fg">{{ basisLabel }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-bo-muted">Ккал</dt>
                                <dd class="mt-1 text-sm text-bo-fg">
                                    {{ nutrition?.calories ?? "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-bo-muted">Белки</dt>
                                <dd class="mt-1 text-sm text-bo-fg">
                                    {{ nutrition?.proteins ?? "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-bo-muted">Жиры</dt>
                                <dd class="mt-1 text-sm text-bo-fg">
                                    {{ nutrition?.fats ?? "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-bo-muted">
                                    Углеводы
                                </dt>
                                <dd class="mt-1 text-sm text-bo-fg">
                                    {{ nutrition?.carbs ?? "—" }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="closeModal">Закрыть</UiButton>
            </template>
        </UiModal>
    </div>
</template>
