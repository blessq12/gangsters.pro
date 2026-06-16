import { computed, ref, watch } from "vue";
import { useCatalogStore } from "../../stores/catalogStore";
import { useUiStore } from "../../stores/uiStore";
import {
    readCatalogSearchHistory,
    rememberCatalogSearchQuery,
} from "./catalogSearchHistory";
import { searchCatalogItems } from "./searchCatalogService";
import { useCatalogSearchDiscover } from "./useCatalogSearchDiscover";

/**
 * @typedef {'idle' | 'loading' | 'results' | 'empty'} CatalogSearchBodyState
 */

const query = ref("");
const showProductDetailModal = ref(false);
const selectedProduct = ref(null);
const searchHistory = ref(readCatalogSearchHistory());
const layerReady = ref(false);
let discoverSeed = Date.now();
let watchersBound = false;

const discover = useCatalogSearchDiscover();

function refreshSearchHistory() {
    searchHistory.value = readCatalogSearchHistory();
}

function persistQueryToHistory(value = query.value) {
    searchHistory.value = rememberCatalogSearchQuery(value);
}

function refreshDiscoverFeed(seed = discoverSeed) {
    const catalogStore = useCatalogStore();

    if (!catalogStore.hasLoaded || catalogStore.categories.length === 0) {
        discover.clearFeed();
        return;
    }

    discover.resetFeed(catalogStore.categories, seed);
}

function isSearchIdleState() {
    const catalogStore = useCatalogStore();
    const loading = !catalogStore.hasLoaded && catalogStore.loading;

    return !loading && query.value.trim().length === 0;
}

function bindCatalogSearchWatchers() {
    if (watchersBound) {
        return;
    }

    watchersBound = true;

    watch(showProductDetailModal, (open) => {
        if (!open) {
            useCatalogStore().setSelectedProduct(null);
            selectedProduct.value = null;
        }
    });

    watch(
        () => useUiStore().catalogSearchOpen,
        (open) => {
            if (open) {
                refreshSearchHistory();
                if (!discover.hasFeed.value) {
                    refreshDiscoverFeed(discoverSeed);
                }
            }
        },
    );

    watch(
        () => useCatalogStore().hasLoaded,
        (loaded) => {
            if (
                loaded
                && useUiStore().catalogSearchOpen
                && isSearchIdleState()
                && !discover.hasFeed.value
            ) {
                refreshDiscoverFeed(discoverSeed);
            }
        },
    );
}

export function useCatalogSearch() {
    bindCatalogSearchWatchers();

    const catalogStore = useCatalogStore();
    const uiStore = useUiStore();

    const isOpen = computed(() => uiStore.catalogSearchOpen);
    const hasQuery = computed(() => query.value.trim().length > 0);

    const results = computed(() =>
        searchCatalogItems(catalogStore.categories, query.value),
    );

    const loading = computed(
        () => !catalogStore.hasLoaded && catalogStore.loading,
    );

    /** @type {import('vue').ComputedRef<CatalogSearchBodyState>} */
    const bodyState = computed(() => {
        if (loading.value) {
            return "loading";
        }
        if (!hasQuery.value) {
            return "idle";
        }
        if (results.value.length === 0) {
            return "empty";
        }
        return "results";
    });

    const idleHint = {
        title: "Поиск по меню",
        lead: "Введи название, ингредиент, тег или категорию.",
        examples: ["филадельфия", "острый", "сет", "ролл"],
    };

    const emptyHint = {
        title: "Ничего не нашли",
        lead: "Попробуй короче или другое слово — по составу, тегу или категории тоже ищем.",
        examples: ["ролл", "сет", "курица"],
    };

    function openSearch() {
        layerReady.value = false;
        discoverSeed = Date.now();
        uiStore.openCatalogSearch();
        query.value = "";
        selectedProduct.value = null;
        showProductDetailModal.value = false;
        refreshSearchHistory();
        refreshDiscoverFeed(discoverSeed);
    }

    function requestCloseSearch() {
        if (hasQuery.value) {
            persistQueryToHistory();
        }
        uiStore.closeCatalogSearch();
    }

    function resetSession() {
        query.value = "";
        selectedProduct.value = null;
        showProductDetailModal.value = false;
        layerReady.value = false;
        discover.clearFeed();
    }

    function clearQuery() {
        query.value = "";
    }

    function applyQuery(value) {
        query.value = String(value ?? "");
    }

    function openProductDetail(product) {
        if (hasQuery.value) {
            persistQueryToHistory();
        }
        catalogStore.setSelectedProduct(product);
        selectedProduct.value = product;
        showProductDetailModal.value = true;
    }

    function markLayerReady() {
        layerReady.value = true;
    }

    return {
        query,
        isOpen,
        hasQuery,
        results,
        loading,
        bodyState,
        idleHint,
        emptyHint,
        searchHistory,
        layerReady,
        discoverItems: discover.visibleItems,
        hasDiscoverFeed: discover.hasFeed,
        loadMoreDiscover: discover.loadMore,
        openSearch,
        requestCloseSearch,
        resetSession,
        clearQuery,
        applyQuery,
        openProductDetail,
        markLayerReady,
        showProductDetailModal,
        selectedProduct,
        desktopCardsPerRow: computed(() => catalogStore.desktopCardsPerRow),
        mobileCardViewMode: computed(() => catalogStore.mobileCardViewMode),
    };
}
