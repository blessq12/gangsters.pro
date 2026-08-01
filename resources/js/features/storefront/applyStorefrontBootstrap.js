import { useCatalogStore } from "../../stores/catalogStore";

/**
 * @param {object|null|undefined} payload
 */
export function applyStorefrontCriticalPayload(payload) {
    if (!payload || typeof payload !== "object") {
        return;
    }

    const catalogStore = useCatalogStore();

    if (payload?.catalog?.categories) {
        catalogStore.applyBootstrapCatalog(payload.catalog);
    }
}

/**
 * @param {object|null|undefined} payload
 */
export function applyStorefrontDeferredPayload(payload) {
    if (!payload || typeof payload !== "object") {
        return;
    }

    const catalogStore = useCatalogStore();

    if (payload?.catalog?.categories) {
        catalogStore.applyBootstrapCatalog(payload.catalog);
    }
}
