<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { createSearchContext } from "../../search/createSearchContext.js";
import { roleHasGlobalSearch } from "../../search/providers/index.js";
import { useGlobalSearch } from "../../composables/useGlobalSearch.js";
import UiInput from "../ui/UiInput.vue";
import ShellIcon from "../ShellIcon.vue";
import GlobalSearchHitRow from "./GlobalSearchHitRow.vue";

const { role, branchId, ensureContext } = createSearchContext();

const visible = computed(() => roleHasGlobalSearch(role.value));

const { query, open, loading, error, groups, hasHits, setQuery, openPanel, closePanel, navigate } =
    useGlobalSearch({
        role,
        branchId,
    });

const rootEl = ref(/** @type {HTMLElement|null} */ (null));
const inputValue = ref("");

watch(inputValue, (value) => {
    setQuery(value);
});

watch(open, async (isOpen) => {
    if (isOpen) {
        await ensureContext();
        inputValue.value = "";
    }
});

/**
 * @param {MouseEvent} event
 */
function onDocumentClick(event) {
    if (!open.value || !rootEl.value) {
        return;
    }
    const target = event.target;
    if (target instanceof Node && !rootEl.value.contains(target)) {
        closePanel();
    }
}

/**
 * @param {KeyboardEvent} event
 */
function onDocumentKeydown(event) {
    if (event.key === "Escape" && open.value) {
        closePanel();
    }
}

onMounted(() => {
    document.addEventListener("click", onDocumentClick);
    document.addEventListener("keydown", onDocumentKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocumentClick);
    document.removeEventListener("keydown", onDocumentKeydown);
});

/**
 * @param {import('../../search/types.js').GlobalSearchHit} hit
 */
function onSelect(hit) {
    navigate(hit);
}
</script>

<template>
    <div v-if="visible" ref="rootEl" class="relative">
        <button
            type="button"
            class="flex h-10 w-10 shrink-0 items-center justify-center text-bo-muted transition-colors hover:bg-white/[0.06] hover:text-bo-fg"
            :class="open ? 'bg-white/[0.06] text-bo-fg' : ''"
            aria-label="Глобальный поиск"
            title="Поиск"
            :aria-expanded="open ? 'true' : 'false'"
            @click.stop="open ? closePanel() : openPanel()"
        >
            <ShellIcon name="search" class="h-5 w-5" />
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-40 mt-2 w-[min(24rem,calc(100vw-2rem))] border border-bo-border bg-bo-elevated shadow-bo-lg"
            @click.stop
        >
            <div class="border-b border-bo-border p-3">
                <UiInput v-model="inputValue" type="search" placeholder="Заказы, клиенты, склад…" />
            </div>

            <div class="max-h-80 overflow-y-auto">
                <p v-if="loading" class="px-3 py-4 text-sm text-bo-muted">Ищем…</p>
                <p v-else-if="error" class="px-3 py-4 text-sm text-red-300">
                    {{ error }}
                </p>
                <p v-else-if="!query.trim()" class="px-3 py-4 text-sm text-bo-muted">
                    Начни вводить запрос
                </p>
                <p v-else-if="!hasHits" class="px-3 py-4 text-sm text-bo-muted">
                    Ничего не найдено
                </p>
                <template v-else>
                    <section
                        v-for="group in groups"
                        :key="group.type"
                        class="border-b border-bo-border last:border-b-0"
                    >
                        <p
                            class="px-3 py-2 text-[11px] font-medium uppercase tracking-wide text-bo-muted"
                        >
                            {{ group.label }}
                        </p>
                        <GlobalSearchHitRow
                            v-for="hit in group.hits"
                            :key="hit.id"
                            :hit="hit"
                            @select="onSelect"
                        />
                    </section>
                </template>
            </div>
        </div>
    </div>
</template>
