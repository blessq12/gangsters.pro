import { computed, onMounted } from "vue";
import { useCatalogStore } from "../../stores/catalogStore";
import { isStorefrontBootstrapPending } from "../shell/isStorefrontBootstrapPending";
import { useStorefrontStore } from "../../stores/storefrontStore";

export function useCatalogReadModel({ autoload = true } = {}) {
    const catalogStore = useCatalogStore();
    const storefrontStore = useStorefrontStore();

    if (autoload) {
        onMounted(() => {
            if (isStorefrontBootstrapPending(storefrontStore)) {
                return;
            }

            if (!catalogStore.hasLoaded && !catalogStore.loading) {
                void catalogStore.fetchAll();
            }
        });
    }

    const categories = computed(() => catalogStore.categories);
    const menuSections = computed(() => catalogStore.menuSections);
    const menuProducts = computed(() => catalogStore.menuProducts);
    const categoryTabs = computed(() => catalogStore.categoryTabs);
    const tagTabs = computed(() => catalogStore.tagTabs);
    const hasLoaded = computed(() => catalogStore.hasLoaded);
    const loading = computed(() => catalogStore.loading);
    const error = computed(() => catalogStore.error);

    return {
        categories,
        menuSections,
        menuProducts,
        categoryTabs,
        tagTabs,
        hasLoaded,
        loading,
        error,
        refresh: () => catalogStore.fetchAll(),
    };
}
