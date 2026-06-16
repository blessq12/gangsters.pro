import { flattenCatalogForSearch } from "./searchCatalogService";

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
