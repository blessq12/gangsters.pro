import { useCheckoutPricingStore } from "../../stores/checkoutPricingStore";

export const CHECKOUT_SESSION_KEY = "gangsters_checkout_session_v1";

export const CHECKOUT_WIZARD_STEPS = ["cart", "guest", "delivery", "payment", "confirm"];

export function readCheckoutSessionPayload() {
    if (typeof window === "undefined") {
        return null;
    }

    try {
        const raw = window.sessionStorage.getItem(CHECKOUT_SESSION_KEY);
        if (!raw) {
            return null;
        }
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === "object" ? parsed : null;
    } catch {
        return null;
    }
}

export function writeCheckoutSessionPayload(payload) {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.setItem(CHECKOUT_SESSION_KEY, JSON.stringify(payload));
}

export function clearCheckoutSessionPayload() {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.removeItem(CHECKOUT_SESSION_KEY);
}

export function buildCheckoutSessionSnapshot(store) {
    const pricingStore = useCheckoutPricingStore();
    const deliveryPricing = pricingStore.deliveryPricing;
    const benefitsProgress = pricingStore.benefitsProgress;
    const promoState = pricingStore.promoState;

    return {
        checkoutId: store.checkoutId,
        status: store.status,
        snapshot: {
            checkout_id: store.checkoutId,
            status: store.status,
            cart: {
                items: store.cartItems.map((item) => ({
                    product_id: item.productId,
                    product_name: item.productSnapshot?.name ?? "",
                    quantity: item.qty,
                    unit_price_rubles: item.productSnapshot?.price ?? 0,
                    line_total_rubles:
                        (Number(item.pricing?.lineTotalKopecks) || 0) / 100,
                    payload: item.payload ?? null,
                })),
                items_total_rubles: store.itemsTotalRubles,
                promo_state: promoState,
            },
            client: store.serverClient,
            delivery: store.serverDelivery,
            payment: store.serverPayment,
            delivery_pricing: deliveryPricing
                ? {
                      method: deliveryPricing.method,
                      items_payable_kopecks: deliveryPricing.itemsPayableKopecks,
                      delivery_fee_kopecks: deliveryPricing.deliveryFeeKopecks,
                      is_free: deliveryPricing.isFree,
                      is_preview: deliveryPricing.isPreview,
                      remaining_to_free_kopecks: deliveryPricing.remainingToFreeKopecks,
                      items_total_kopecks: deliveryPricing.itemsTotalKopecks,
                      grand_total_kopecks: deliveryPricing.grandTotalKopecks,
                      items_total_rub: deliveryPricing.itemsTotalRub,
                      delivery_fee_rub: deliveryPricing.deliveryFeeRub,
                      grand_total_rub: deliveryPricing.grandTotalRub,
                  }
                : null,
            benefits_progress: benefitsProgress,
        },
    };
}
