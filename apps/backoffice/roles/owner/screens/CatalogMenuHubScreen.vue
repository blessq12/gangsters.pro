<script setup>
import { computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import CatalogScreen from "./CatalogScreen.vue";
import MenuScreen from "./MenuScreen.vue";

const route = useRoute();
const router = useRouter();

const sections = [
    { id: "catalog", label: "Справочник" },
    { id: "branch-menu", label: "Меню филиала" },
];

const section = computed(() =>
    route.query.section === "branch-menu" ? "branch-menu" : "catalog",
);

function setSection(id) {
    if (section.value === id) {
        return;
    }
    router.replace({
        name: "owner-menu",
        query: id === "catalog" ? {} : { section: id },
    });
}

watch(
    () => route.query.section,
    (value) => {
        if (value && value !== "catalog" && value !== "branch-menu") {
            router.replace({ name: "owner-menu", query: {} });
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">
                    Меню
                </h1>
                <p class="mt-1 text-sm text-bo-muted">
                    Справочник товаров общий; продажа и цена — в меню выбранного филиала.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in sections"
                    :key="item.id"
                    type="button"
                    class="h-10 px-4 text-sm font-medium transition-colors duration-150"
                    :class="section === item.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="setSection(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>
        </div>

        <CatalogScreen v-if="section === 'catalog'" embedded />
        <MenuScreen v-else embedded />
    </div>
</template>
