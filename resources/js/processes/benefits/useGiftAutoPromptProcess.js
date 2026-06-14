import { useUiStore } from "../../stores/uiStore";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";

let processInitialized = false;
let cleanupHandlers = [];

export function useGiftAutoPromptProcess() {
    if (!processInitialized) {
        const uiStore = useUiStore();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.BENEFIT_GIFT_LOST, () => {
                uiStore.resetGiftAutoPromptDismissed();
                uiStore.closeGiftSelectionModal({ dismissAuto: false });
            }),
        ];

        processInitialized = true;
    }

    return {
        dispose() {
            cleanupHandlers.forEach((cleanup) => cleanup());
            cleanupHandlers = [];
            processInitialized = false;
        },
    };
}
