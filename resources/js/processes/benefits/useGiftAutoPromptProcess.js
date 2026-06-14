import { watch } from "vue";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useUiStore } from "../../stores/uiStore";

let processInitialized = false;
let stopWatch = null;

export function useGiftAutoPromptProcess() {
    if (!processInitialized) {
        const uiStore = useUiStore();
        const cartStore = useCheckoutStore();

        stopWatch = watch(
            () => Boolean(cartStore.benefitsProgress?.gift?.isReached),
            (isReached, wasReached) => {
                if (wasReached === true && isReached === false) {
                    uiStore.resetGiftAutoPromptDismissed();
                    uiStore.closeGiftSelectionModal({ dismissAuto: false });
                }
            },
        );

        processInitialized = true;
    }

    return {
        dispose() {
            stopWatch?.();
            stopWatch = null;
            processInitialized = false;
        },
    };
}
