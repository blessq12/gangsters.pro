<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import UiButton from "../ui/UiButton.vue";
import UiInput from "../ui/UiInput.vue";
import { useBranchMenuSearch } from "../../composables/useBranchMenuSearch.js";
import BranchMenuProductTile from "./BranchMenuProductTile.vue";

const props = defineProps({
    /** Категории меню филиала (предпочтительно) */
    categories: { type: Array, default: () => [] },
    /** Плоский fallback, если категорий нет */
    menuItems: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["add"]);

const pickerOpen = ref(false);
const rootEl = ref(/** @type {HTMLElement|null} */ (null));

const categoriesForSearch = computed(() => {
    if (Array.isArray(props.categories) && props.categories.length > 0) {
        return props.categories;
    }
    const items = Array.isArray(props.menuItems) ? props.menuItems : [];
    if (items.length === 0) {
        return [];
    }
    return [{ id: "all", label: "Меню", items }];
});

const { query, dropdownHits, resetSearch } = useBranchMenuSearch({
    categories: categoriesForSearch,
});

function openPicker() {
    if (props.disabled) {
        return;
    }
    pickerOpen.value = true;
    resetSearch();
}

function closePicker() {
    pickerOpen.value = false;
    resetSearch();
}

/**
 * @param {Record<string, unknown>} item
 */
function pickItem(item) {
    emit("add", {
        id: Number(item.id),
        kind: String(item.kind || "product"),
        name: String(item.name || `Товар #${item.id}`),
    });
    closePicker();
}

/**
 * @param {MouseEvent} event
 */
function onDocumentClick(event) {
    if (!pickerOpen.value || !rootEl.value) {
        return;
    }
    const target = event.target;
    if (target instanceof Node && !rootEl.value.contains(target)) {
        closePicker();
    }
}

watch(
    () => props.disabled,
    (value) => {
        if (value) {
            closePicker();
        }
    },
);

onMounted(() => {
    document.addEventListener("click", onDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocumentClick);
});
</script>

<template>
    <div ref="rootEl" class="relative">
        <UiButton
            class="!h-11 !bg-white/10 !text-bo-fg"
            :disabled="disabled"
            @click.stop="openPicker"
        >
            Добавить товар
        </UiButton>

        <div
            v-if="pickerOpen"
            class="absolute left-0 right-0 z-30 mt-2 border border-bo-border bg-bo-elevated shadow-bo-lg"
            @click.stop
        >
            <div class="border-b border-bo-border p-3">
                <UiInput
                    v-model="query"
                    type="search"
                    placeholder="Поиск: название, состав, техкарта"
                />
            </div>
            <p v-if="loading" class="px-3 py-4 text-sm text-bo-muted">Загрузка меню…</p>
            <p v-else-if="dropdownHits.length === 0" class="px-3 py-4 text-sm text-bo-muted">
                Ничего не найдено
            </p>
            <ul v-else class="max-h-64 overflow-y-auto">
                <li v-for="item in dropdownHits" :key="item.key || `${item.kind}:${item.id}`">
                    <BranchMenuProductTile :item="item" dense @select="pickItem" />
                </li>
            </ul>
        </div>
    </div>
</template>
