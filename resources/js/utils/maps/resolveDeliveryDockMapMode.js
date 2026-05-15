/**
 * Режим карты в доке «Оплата и доставка».
 *
 * @typedef {'zone-sdk' | 'widget' | 'loading' | 'fallback'} DeliveryDockMapMode
 */

/**
 * @param {{
 *   hasApiKey: boolean,
 *   hasAddress: boolean,
 *   isLoading: boolean,
 * }} input
 * @returns {DeliveryDockMapMode}
 */
export function resolveDeliveryDockMapMode(input) {
    if (input.hasApiKey) {
        return "zone-sdk";
    }
    if (input.hasAddress) {
        return "widget";
    }
    if (input.isLoading) {
        return "loading";
    }
    return "fallback";
}
