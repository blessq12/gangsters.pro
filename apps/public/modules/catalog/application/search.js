import { computed, ref, watch } from "vue";
import { useCatalogStore } from "../store";
import { useUiStore } from "../../shell/store/uiStore";

const CATALOG_SEARCH_HISTORY_KEY = "gangsters_catalog_search_history_v1";
const MAX_HISTORY_ENTRIES = 8;
const MIN_QUERY_LENGTH = 2;

/**
 * @returns {string[]}
 */
export function readCatalogSearchHistory() {
    if (typeof window === "undefined") {
        return [];
    }

    try {
        const raw = window.localStorage.getItem(CATALOG_SEARCH_HISTORY_KEY);
        if (!raw) {
            return [];
        }

        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) {
            return [];
        }

        return parsed
            .map((entry) => String(entry || "").trim())
            .filter((entry) => entry.length >= MIN_QUERY_LENGTH)
            .slice(0, MAX_HISTORY_ENTRIES);
    } catch {
        return [];
    }
}

/**
 * @param {string} query
 * @returns {string[]}
 */
export function rememberCatalogSearchQuery(query) {
    const normalized = String(query ?? "").trim();
    if (normalized.length < MIN_QUERY_LENGTH) {
        return readCatalogSearchHistory();
    }

    const previous = readCatalogSearchHistory().filter(
        (entry) => entry.toLowerCase() !== normalized.toLowerCase(),
    );
    const next = [normalized, ...previous].slice(0, MAX_HISTORY_ENTRIES);

    if (typeof window !== "undefined") {
        window.localStorage.setItem(CATALOG_SEARCH_HISTORY_KEY, JSON.stringify(next));
    }

    return next;
}

/**
 * Клиентский поиск по загруженному дереву каталога.
 */

/**
 * @param {unknown} value
 */
function normalizeSearchText(value) {
    return String(value ?? "")
        .toLowerCase()
        .replace(/ё/g, "е")
        .trim();
}

/**
 * @param {object} product
 * @param {string[]} categoryNames
 * @returns {string[]}
 */
function collectSearchableParts(product, categoryNames) {
    const parts = [];

    const push = (value) => {
        const normalized = normalizeSearchText(value);
        if (normalized) {
            parts.push(normalized);
        }
    };

    push(product?.name);
    push(product?.slug);
    push(product?.description);

    const raw = product?.raw;
    if (raw && typeof raw === "object") {
        push(raw.name);
        push(raw.slug);
        push(raw.description);
        push(raw.ingredients);
        push(raw.sku);
        push(raw.weight);
    }

    const tags = Array.isArray(product?.tags) ? product.tags : [];
    for (const tag of tags) {
        push(tag?.label);
        push(tag?.code);
    }

    const lines = Array.isArray(product?.lines) ? product.lines : [];
    for (const line of lines) {
        push(line?.product_name);
    }

    if (product?.nutrition && typeof product.nutrition === "object") {
        const n = product.nutrition;
        push(
            [n.calories, n.proteins, n.fats, n.carbs, n.basis]
                .filter((entry) => entry != null && entry !== "")
                .join(" "),
        );
    }

    for (const categoryName of categoryNames) {
        push(categoryName);
    }

    return [...new Set(parts)];
}

/**
 * @param {Array<{ category?: object, products?: object[] }>} categories
 * @returns {Array<{ product: object, categoryNames: string[] }>}
 */
export function flattenCatalogForSearch(categories) {
    if (!Array.isArray(categories)) {
        return [];
    }

    /** @type {Map<string, { product: object, categoryNames: Set<string> }>} */
    const indexed = new Map();

    for (const entry of categories) {
        const categoryName = String(entry?.category?.name || "").trim();
        const products = Array.isArray(entry?.products) ? entry.products : [];

        for (const product of products) {
            if (!product || product.id == null) {
                continue;
            }

            const key = `${String(product.kind || "product")}:${Number(product.id)}`;
            const existing = indexed.get(key);

            if (existing) {
                if (categoryName) {
                    existing.categoryNames.add(categoryName);
                }
                continue;
            }

            indexed.set(key, {
                product,
                categoryNames: categoryName ? new Set([categoryName]) : new Set(),
            });
        }
    }

    return Array.from(indexed.values()).map((entry) => ({
        product: entry.product,
        categoryNames: Array.from(entry.categoryNames),
    }));
}

/**
 * @param {object} product
 * @param {string} query
 * @param {string[]} parts
 */
function scoreCatalogSearchHit(product, query, parts) {
    const q = normalizeSearchText(query);
    if (!q) {
        return 0;
    }

    const name = normalizeSearchText(product?.name);

    if (name === q) {
        return 100;
    }
    if (name.startsWith(q)) {
        return 80;
    }
    if (name.includes(q)) {
        return 60;
    }

    if (parts.some((part) => part.includes(q))) {
        return 40;
    }

    const tokens = q.split(/\s+/).filter(Boolean);
    if (
        tokens.length > 1
        && tokens.every((token) => parts.some((part) => part.includes(token)))
    ) {
        return 30;
    }

    return 0;
}

/**
 * @param {Array<{ category?: object, products?: object[] }>} categories
 * @param {string} query
 * @returns {object[]}
 */
export function searchCatalogItems(categories, query) {
    const trimmed = String(query ?? "").trim();
    if (!trimmed) {
        return [];
    }

    const hits = flattenCatalogForSearch(categories)
        .map(({ product, categoryNames }) => {
            const parts = collectSearchableParts(product, categoryNames);
            const score = scoreCatalogSearchHit(product, trimmed, parts);

            return { product, score };
        })
        .filter((entry) => entry.score > 0)
        .sort((a, b) => {
            if (b.score !== a.score) {
                return b.score - a.score;
            }

            return String(a.product?.name || "").localeCompare(
                String(b.product?.name || ""),
                "ru",
            );
        });

    return hits.map((entry) => entry.product);
}

/**
 * @param {number} seed
 */
function createSeededRandom(seed) {
    let state = seed >>> 0;

    return () => {
        state += 0x6d2b79f5;
        let t = state;
        t = Math.imul(t ^ (t >>> 15), t | 1);
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

/**
 * @template T
 * @param {T[]} items
 * @param {number} seed
 * @returns {T[]}
 */
export function shuffleCatalogDiscoverPool(items, seed) {
    if (!Array.isArray(items) || items.length <= 1) {
        return Array.isArray(items) ? [...items] : [];
    }

    const random = createSeededRandom(seed);
    const next = [...items];

    for (let index = next.length - 1; index > 0; index -= 1) {
        const swapIndex = Math.floor(random() * (index + 1));
        [next[index], next[swapIndex]] = [next[swapIndex], next[index]];
    }

    return next;
}

/**
 * @param {Array<{ category?: object, products?: object[] }>} categories
 * @returns {object[]}
 */
export function buildCatalogDiscoverPool(categories) {
    return flattenCatalogForSearch(categories).map((entry) => entry.product);
}

/**
 * @param {object[]} pool
 * @param {number} [seed]
 */
export function createCatalogDiscoverIterator(pool, seed = Date.now()) {
    const ordered = shuffleCatalogDiscoverPool(pool, seed);
    let cursor = 0;

    return {
        get size() {
            return ordered.length;
        },
        /**
         * @param {number} count
         * @returns {object[]}
         */
        take(count) {
            if (ordered.length === 0 || count <= 0) {
                return [];
            }

            const chunk = [];
            for (let index = 0; index < count; index += 1) {
                chunk.push(ordered[cursor % ordered.length]);
                cursor += 1;
            }

            return chunk;
        },
    };
}

const INITIAL_CHUNK_SIZE = 12;
const LOAD_MORE_CHUNK_SIZE = 12;

/** @type {import('vue').Ref<object[]>} */
const visibleItems = ref([]);
/** @type {ReturnType<typeof createCatalogDiscoverIterator> | null} */
let iterator = null;
let appendLocked = false;

const hasFeed = computed(() => visibleItems.value.length > 0);

function resetFeed(categories, seed = Date.now()) {
    const pool = buildCatalogDiscoverPool(categories);
    iterator = createCatalogDiscoverIterator(pool, seed);
    visibleItems.value = iterator.take(INITIAL_CHUNK_SIZE);
}

function loadMore() {
    if (appendLocked || !iterator || iterator.size === 0) {
        return;
    }

    appendLocked = true;
    const nextItems = iterator.take(LOAD_MORE_CHUNK_SIZE);
    if (nextItems.length > 0) {
        visibleItems.value = [...visibleItems.value, ...nextItems];
    }
    appendLocked = false;
}

function clearFeed() {
    iterator = null;
    appendLocked = false;
    visibleItems.value = [];
}

export function useCatalogSearchDiscover() {
    return {
        visibleItems,
        hasFeed,
        resetFeed,
        loadMore,
        clearFeed,
    };
}

export const CatalogSearchActionSourceKey = Symbol("catalogSearchActionSource");

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
