<script setup>
import { computed, defineAsyncComponent, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import OwnerBranchSelector from "../components/branch/OwnerBranchSelector.vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";
import CatalogScreen from "./CatalogScreen.vue";

/** Branch-панели с vuedraggable — лениво, чтобы контекст «Справочник» не падал на outdated optimize dep. */
const BranchCategoriesPanel = defineAsyncComponent(
    () => import("../components/menu/BranchCategoriesPanel.vue"),
);
const BranchSetsPanel = defineAsyncComponent(
    () => import("../components/menu/BranchSetsPanel.vue"),
);
const BranchTagsPanel = defineAsyncComponent(
    () => import("../components/menu/BranchTagsPanel.vue"),
);
const MenuScreen = defineAsyncComponent(() => import("./MenuScreen.vue"));

const route = useRoute();
const router = useRouter();
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();

ensureLoaded();

/** Два верхних контекста: глобальный справочник vs меню филиала. */
const contexts = [
    { id: "catalog", label: "Справочник товаров" },
    { id: "branch", label: "Меню филиала" },
];

const branchSections = [
    { id: "prices", label: "Цены и доступность" },
    { id: "categories", label: "Категории" },
    { id: "tags", label: "Теги" },
    { id: "sets", label: "Наборы" },
];

const contextIds = contexts.map((item) => item.id);
const branchSectionIds = branchSections.map((item) => item.id);

const context = computed(() =>
    contextIds.includes(String(route.query.ctx)) ? String(route.query.ctx) : "catalog",
);

const branchSection = computed(() =>
    branchSectionIds.includes(String(route.query.section)) ? String(route.query.section) : "prices",
);

function setContext(id) {
    if (context.value === id) {
        return;
    }
    if (id === "catalog") {
        router.replace({ name: "owner-menu", query: {} });
        return;
    }
    router.replace({
        name: "owner-menu",
        query: { ctx: "branch", section: "prices" },
    });
}

function setBranchSection(id) {
    if (branchSection.value === id) {
        return;
    }
    router.replace({
        name: "owner-menu",
        query: { ctx: "branch", section: id },
    });
}

watch(
    () => [route.query.ctx, route.query.section],
    ([ctx, section]) => {
        if (ctx && !contextIds.includes(String(ctx))) {
            router.replace({ name: "owner-menu", query: {} });
            return;
        }
        if (String(ctx) === "branch" && section && !branchSectionIds.includes(String(section))) {
            router.replace({ name: "owner-menu", query: { ctx: "branch", section: "prices" } });
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <div class="space-y-3">
            <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">Каталог</h1>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in contexts"
                    :key="item.id"
                    type="button"
                    class="h-11 px-4 text-sm font-medium transition-colors duration-150"
                    :class="
                        context === item.id
                            ? 'bg-bo-accent text-white'
                            : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'
                    "
                    @click="setContext(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>
        </div>

        <CatalogScreen v-if="context === 'catalog'" embedded />

        <template v-else>
            <OwnerBranchSelector />

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in branchSections"
                    :key="item.id"
                    type="button"
                    class="h-10 px-3.5 text-sm font-medium transition-colors duration-150"
                    :class="
                        branchSection === item.id
                            ? 'bg-white/15 text-bo-fg'
                            : 'bg-transparent text-bo-muted hover:bg-white/[0.06] hover:text-bo-fg'
                    "
                    @click="setBranchSection(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>

            <template v-if="selectedBranchId">
                <MenuScreen v-if="branchSection === 'prices'" embedded />
                <BranchCategoriesPanel v-else-if="branchSection === 'categories'" embedded />
                <BranchTagsPanel v-else-if="branchSection === 'tags'" embedded />
                <BranchSetsPanel v-else embedded />
            </template>
        </template>
    </div>
</template>
