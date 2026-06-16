import { watch } from "vue";
import {
    getIntroSceneDurationSec,
    INTRO_DOCK_REVEAL_GAP_MS,
} from "../../animations/animationManager";
import { useCompanyStore } from "../../stores/companyStore";
import { useStorefrontStore } from "../../stores/storefrontStore";
import { useUiStore } from "../../stores/uiStore";
import { isCompanyOpenNow } from "../../utils/system/companyOpenStatus";
import { wasClosedNoticeDismissedThisSession } from "../../utils/system/closedOrdersNotice";

let processInitialized = false;
let stopWatch = null;
/** @type {ReturnType<typeof setTimeout> | null} */
let revealTimer = null;
let noticeScheduled = false;

function getShellRevealDelayMs() {
    return Math.round(getIntroSceneDurationSec() * 1000) + INTRO_DOCK_REVEAL_GAP_MS;
}

export function useCompanyClosedNoticeProcess() {
    if (!processInitialized) {
        const uiStore = useUiStore();
        const companyStore = useCompanyStore();
        const storefrontStore = useStorefrontStore();

        function tryOpenNotice() {
            if (!storefrontStore.loaded || !companyStore.profile) {
                return;
            }

            if (isCompanyOpenNow(companyStore.profile)) {
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

            if (!storefrontStore.loaded || !companyStore.profile) {
                return;
            }

            noticeScheduled = true;
            revealTimer = setTimeout(() => {
                revealTimer = null;
                tryOpenNotice();
            }, getShellRevealDelayMs());
        }

        stopWatch = watch(
            () => [storefrontStore.loaded, companyStore.profile],
            () => scheduleNoticeAfterShellReady(),
            { immediate: true },
        );

        processInitialized = true;
    }

    return {
        dispose() {
            stopWatch?.();
            stopWatch = null;
            if (revealTimer != null) {
                clearTimeout(revealTimer);
                revealTimer = null;
            }
            noticeScheduled = false;
            processInitialized = false;
        },
    };
}
