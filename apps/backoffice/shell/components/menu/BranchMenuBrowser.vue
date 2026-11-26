<script setup>
import { ref, toRef, watch } from "vue";
import UiInput from "../ui/UiInput.vue";
import { useBranchMenuSearch } from "../../composables/useBranchMenuSearch.js";
import BranchMenuProductDetail from "./BranchMenuProductDetail.vue";
import BranchMenuProductTile from "./BranchMenuProductTile.vue";

const props = defineProps({
    categories: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    /**
     * chips — чипы сверху (компактный режим);
     * sidebar — колонка категорий слева + сетка справа (модалка).
     */
    layout: {
        type: String,
        default: "chips",
        validator: (value) => ["chips", "sidebar"].includes(value),
    },
});

const emit = defineEmits(["select"]);

const selectedItem = ref(/** @type {Record<string, unknown>|null} */ (null));

const { query, categoryId, filteredCategories, currentCategory, resultItems } = useBranchMenuSearch(
    {
        categories: toRef(props, "categories"),
    },
);

watch(
    () => [query.value, categoryId.value],
    () => {
        selectedItem.value = null;
    },
);

/**
 * @param {Record<string, unknown>} item
 */
function onSelect(item) {
    selectedItem.value = item;
    emit("select", item);
}

function clearSelection() {
    selectedItem.value = null;
}
</script>

<template>
    <div class="flex h-full min-h-0 flex-col gap-3">
        <UiInput v-model="query" type="search" placeholder="Поиск: название, состав, техкарта" />

        <p v-if="loading" class="text-sm text-bo-muted">Загрузка меню…</p>

        <template v-else-if="filteredCategories.length === 0">
            <p class="text-sm text-bo-muted">
                {{ query.trim() ? "Ничего не найдено" : "Нет доступных позиций в меню филиала." }}
            </p>
        </template>

        <template v-else-if="layout === 'sidebar'">
            <div class="flex min-h-0 flex-1 gap-4 overflow-hidden">
                <aside
                    class="flex w-48 shrink-0 flex-col overflow-hidden border border-bo-border bg-bo-field/40 sm:w-56"
                >
                    <p
                        class="shrink-0 border-b border-bo-border px-3 py-2 text-[11px] font-medium uppercase tracking-wide text-bo-muted"
                    >
                        Категории
                    </p>
                    <nav class="min-h-0 flex-1 overflow-y-auto p-1">
                        <button
                            v-for="category in filteredCategories"
                            :key="category.id"
                            type="button"
                            class="flex w-full items-center px-3 py-2.5 text-left text-sm transition-colors"
                            :class="
                                (currentCategory && currentCategory.id) === category.id
                                    ? 'bg-bo-accent text-white'
                                    : 'text-bo-muted hover:bg-white/[0.06] hover:text-bo-fg'
                            "
                            @click="categoryId = category.id"
                        >
                            <span class="truncate">{{ category.label }}</span>
                        </button>
                    </nav>
                </aside>

                <div class="min-h-0 min-w-0 flex-1 overflow-hidden">
                    <BranchMenuProductDetail
                        v-if="selectedItem"
                        :item="selectedItem"
                        @back="clearSelection"
                    />
                    <div v-else class="h-full overflow-y-auto pr-1">
                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
                            <BranchMenuProductTile
                                v-for="item in resultItems"
                                :key="item.key"
                                :item="item"
                                @select="onSelect"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="category in filteredCategories"
                    :key="category.id"
                    type="button"
                    class="h-9 px-3 text-sm font-medium transition-colors"
                    :class="
                        (currentCategory && currentCategory.id) === category.id
                            ? 'bg-bo-accent text-white'
                            : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'
                    "
                    @click="categoryId = category.id"
                >
                    {{ category.label }}
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-auto">
                <div v-if="selectedItem" class="h-full">
                    <BranchMenuProductDetail :item="selectedItem" @back="clearSelection" />
                </div>
                <div v-else class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <BranchMenuProductTile
                        v-for="item in resultItems"
                        :key="item.key"
                        :item="item"
                        @select="onSelect"
                    />
                </div>
            </div>
        </template>
    </div>
</template>
