import { useCatalogStore } from "../../stores/catalogStore";
import { useCompanyStore } from "../../stores/companyStore";
import { useDeliveryStore } from "../../stores/deliveryStore";
import { useMarketingStore } from "../../stores/marketingStore";

/**
 * @param {object|null|undefined} payload
 */
export function applyStorefrontCriticalPayload(payload) {
    if (!payload || typeof payload !== "object") {
        return;
    }

    const catalogStore = useCatalogStore();
    const deliveryStore = useDeliveryStore();
    const companyStore = useCompanyStore();
    const marketingStore = useMarketingStore();

    if (payload?.catalog?.categories) {
        catalogStore.applyBootstrapCatalog(payload.catalog);
    }
    if (payload?.delivery != null) {
        deliveryStore.data = payload.delivery;
    }
    if (payload?.company != null) {
        companyStore.applyBootstrap(payload.company);
    }
    if (payload?.marketing != null) {
        marketingStore.applyBootstrap(payload.marketing);
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
    const deliveryStore = useDeliveryStore();
    const companyStore = useCompanyStore();
    const marketingStore = useMarketingStore();

    if (payload?.catalog?.categories) {
        catalogStore.applyBootstrapCatalog(payload.catalog);
    }
    if (payload?.delivery != null) {
        deliveryStore.applyDeferredBootstrap(payload.delivery);
    }
    if (payload?.company != null) {
        companyStore.applyBootstrap(payload.company);
    }
    if (payload?.marketing != null) {
        marketingStore.applyBootstrap(payload.marketing);
    }
}
