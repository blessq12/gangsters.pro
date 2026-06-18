import { computed, ref } from "vue";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useUiStore } from "../../stores/uiStore";
import {
    DOCK_DISMISS_KIND,
    resolveDockDismissPolicy,
} from "./dockDismissPolicy";

export function useDockDismiss() {
    const uiStore = useUiStore();
    const checkoutStore = useCheckoutStore();
    const pendingConfirm = ref(null);

    const isPanelOpen = computed(() => uiStore.dockActiveId !== null);

    const showScrim = computed(
        () => isPanelOpen.value && pendingConfirm.value === null,
    );

    const confirmOpen = computed({
        get() {
            return pendingConfirm.value !== null;
        },
        set(next) {
            if (!next) {
                pendingConfirm.value = null;
            }
        },
    });

    function isDismissBlocked() {
        return (
            uiStore.showGiftSelectionModal
            || uiStore.showClosedForOrdersModal
            || uiStore.catalogSearchOpen
        );
    }

    function executeDismiss() {
        uiStore.closeDockPanel();
        pendingConfirm.value = null;
    }

    function requestDockDismiss() {
        if (!uiStore.dockActiveId) {
            return;
        }

        if (isDismissBlocked()) {
            return;
        }

        const policy = resolveDockDismissPolicy({
            dockActiveId: uiStore.dockActiveId,
            checkoutWizardStep: uiStore.checkoutWizardStep,
            checkoutStore,
        });

        if (policy.kind === DOCK_DISMISS_KIND.IMMEDIATE) {
            executeDismiss();
            return;
        }

        pendingConfirm.value = policy;
    }

    function confirmDismiss() {
        executeDismiss();
    }

    function cancelDismiss() {
        pendingConfirm.value = null;
    }

    return {
        isPanelOpen,
        showScrim,
        confirmOpen,
        pendingConfirm,
        requestDockDismiss,
        confirmDismiss,
        cancelDismiss,
    };
}
