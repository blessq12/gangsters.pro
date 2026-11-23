<script setup>
import { computed, ref } from "vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const props = defineProps({
    /** @type {{ id: string, label: string, items: Array<{ key: string, id: number, kind: string, name: string, price_rubles: number }> }[]} */
    categories: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(["add"]);

const activeCategoryId = ref("");
const filter = ref("");

const visibleCategories = computed(() => {
    const q = filter.value.trim().toLowerCase();
    return (props.categories || [])
        .map((category) => {
            const items = (category.items || []).filter((item) => {
                if (!q) {
                    return true;
                }
                return String(item.name || "").toLowerCase().includes(q);
            });
            return { ...category, items };
        })
        .filter((category) => category.items.length > 0);
});

const currentCategory = computed(() => {
    if (visibleCategories.value.length === 0) {
        return null;
    }
    const found = visibleCategories.value.find((item) => item.id === activeCategoryId.value);
    return found || visibleCategories.value[0];
});

/**
 * @param {{ id: number, kind: string, name: string, price_rubles: number }} item
 */
function add(item) {
    emit("add", item);
}
</script>

<template>
    <div class="flex h-full min-h-0 flex-col gap-3">
        <UiInput
            v-model="filter"
            type="search"
            placeholder="Поиск в меню"
        />

        <p
            v-if="loading"
            class="text-sm text-bo-muted"
        >
            Загрузка меню…
        </p>

        <template v-else-if="visibleCategories.length === 0">
            <p class="text-sm text-bo-muted">
                Нет доступных позиций в меню филиала.
            </p>
        </template>

        <template v-else>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="category in visibleCategories"
                    :key="category.id"
                    type="button"
                    class="h-9 px-3 text-sm font-medium transition-colors"
                    :class="(currentCategory && currentCategory.id) === category.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="activeCategoryId = category.id"
                >
                    {{ category.label }}
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-auto">
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    <button
                        v-for="item in (currentCategory?.items || [])"
                        :key="item.key"
                        type="button"
                        class="border border-bo-border bg-bo-field p-3 text-left transition-colors hover:border-bo-border-strong hover:bg-white/[0.04]"
                        @click="add(item)"
                    >
                        <div class="text-sm font-medium text-bo-fg">
                            {{ item.name }}
                        </div>
                        <div class="mt-1 text-xs text-bo-muted">
                            {{ item.kind === 'set' ? 'Набор' : 'Товар' }} · {{ item.price_rubles }} ₽
                        </div>
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
