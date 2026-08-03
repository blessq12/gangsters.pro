import { watch } from "vue";
import { INTRO_DOCK_REVEAL_GAP_MS } from "../../../animations/animationManager";
import {
    DOMAIN_EVENTS,
    emitDomainEvent,
    subscribeDomainEvent,
} from "../../../platform/domainEvents";
import {
    isCompanyOpenNow,
    wasClosedNoticeDismissedThisSession,
} from "../../content/application/company";
import { useContentStore } from "../../content/store";
import { useCheckoutStore } from "../../checkout/store";
import { useShellStore } from "../store/shellStore";
import { useUiStore } from "../store/uiStore";

let sessionProcessInitialized = false;
let sessionCleanupHandlers = [];

export function useSessionLifecycleProcess() {
    if (!sessionProcessInitialized) {
        const uiStore = useUiStore();

        sessionCleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT, () => {
                useCheckoutStore().clearAfterCompleted();
                uiStore.setDockActive(null);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, () => {
                useCheckoutStore().clearAfterCompleted();
                uiStore.closeGiftSelectionModal({ dismissAuto: false });
                uiStore.resetGiftAutoPromptDismissed();
                emitDomainEvent(DOMAIN_EVENTS.CART_CLEARED);
            }),
        ];

        sessionProcessInitialized = true;
    }

    return {
        dispose() {
            sessionCleanupHandlers.forEach((cleanup) => cleanup());
            sessionCleanupHandlers = [];
            sessionProcessInitialized = false;
        },
    };
}

let giftProcessInitialized = false;
let giftStopWatch = null;

export function useGiftAutoPromptProcess() {
    if (!giftProcessInitialized) {
        const uiStore = useUiStore();
        const cartStore = useCheckoutStore();

        giftStopWatch = watch(
            () => Boolean(cartStore.benefitsProgress?.gift?.isReached),
            (isReached, wasReached) => {
                if (wasReached === true && isReached === false) {
                    uiStore.resetGiftAutoPromptDismissed();
                    uiStore.closeGiftSelectionModal({ dismissAuto: false });
                }
            },
        );

        giftProcessInitialized = true;
    }

    return {
        dispose() {
            giftStopWatch?.();
            giftStopWatch = null;
            giftProcessInitialized = false;
        },
    };
}

let closedNoticeProcessInitialized = false;
let closedNoticeStopWatch = null;
/** @type {ReturnType<typeof setTimeout> | null} */
let revealTimer = null;
let noticeScheduled = false;

export function useCompanyClosedNoticeProcess() {
    if (!closedNoticeProcessInitialized) {
        const uiStore = useUiStore();
        const contentStore = useContentStore();
        const shellStore = useShellStore();

        function tryOpenNotice() {
            if (!shellStore.isInteractive || !contentStore.loaded || !contentStore.profile) {
                return;
            }

            if (isCompanyOpenNow(contentStore.profile)) {
                return;
            }

            if (wasClosedNoticeDismissedThisSession()) {
                return;
            }

            if (uiStore.showClosedForOrdersModal) {
                return;
            }

            uiStore.openClosedForOrdersModal();
        }

        function scheduleNoticeAfterShellReady() {
            if (noticeScheduled) {
                return;
            }

            if (!shellStore.isInteractive || !contentStore.loaded || !contentStore.profile) {
                return;
            }

            noticeScheduled = true;
            revealTimer = setTimeout(() => {
                revealTimer = null;
                tryOpenNotice();
            }, INTRO_DOCK_REVEAL_GAP_MS);
        }

        closedNoticeStopWatch = watch(
            () => [
                shellStore.isInteractive,
                contentStore.loaded,
                contentStore.profile,
            ],
            () => scheduleNoticeAfterShellReady(),
            { immediate: true },
        );

        closedNoticeProcessInitialized = true;
    }

    return {
        dispose() {
            closedNoticeStopWatch?.();
            closedNoticeStopWatch = null;
            if (revealTimer != null) {
                clearTimeout(revealTimer);
                revealTimer = null;
            }
            noticeScheduled = false;
            closedNoticeProcessInitialized = false;
        },
    };
}
