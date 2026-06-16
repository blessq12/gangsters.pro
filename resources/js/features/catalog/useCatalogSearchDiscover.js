import { computed, ref } from "vue";
import {
    buildCatalogDiscoverPool,
    createCatalogDiscoverIterator,
} from "./catalogDiscoverFeedService";

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
