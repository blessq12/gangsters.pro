import { refreshOrderDraftPreview } from "./checkoutSessionService";

/**
 * Debounced вызов preview для шага доставки.
 */
export function createOrderDraftPreviewScheduler(store) {
    let timer = null;

    return {
        schedule(selectedAddress = null, delayMs = 450) {
            clearTimeout(timer);
            timer = setTimeout(() => {
                void refreshOrderDraftPreview(store, selectedAddress).catch(() => {});
            }, delayMs);
        },

        cancel() {
            clearTimeout(timer);
            timer = null;
        },

        flush(selectedAddress = null) {
            clearTimeout(timer);
            timer = null;

            return refreshOrderDraftPreview(store, selectedAddress);
        },
    };
}
