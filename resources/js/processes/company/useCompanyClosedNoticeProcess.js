import { watch } from "vue";
import { INTRO_DOCK_REVEAL_GAP_MS } from "../../animations/animationManager";
import { useCompanyStore } from "../../stores/companyStore";
import { useContentStore } from "../../stores/contentStore";
import { useShellStore } from "../../stores/shellStore";
import { useUiStore } from "../../stores/uiStore";
import { isCompanyOpenNow } from "../../utils/system/companyOpenStatus";
import { wasClosedNoticeDismissedThisSession } from "../../utils/system/closedOrdersNotice";

let processInitialized = false;
let stopWatch = null;
/** @type {ReturnType<typeof setTimeout> | null} */
let revealTimer = null;
let noticeScheduled = false;

export function useCompanyClosedNoticeProcess() {
    if (!processInitialized) {
        const uiStore = useUiStore();
        const companyStore = useCompanyStore();
        const contentStore = useContentStore();
        const shellStore = useShellStore();

        function tryOpenNotice() {
            if (!shellStore.isInteractive || !contentStore.loaded || !companyStore.profile) {
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

            if (!shellStore.isInteractive || !contentStore.loaded || !companyStore.profile) {
                return;
            }

            noticeScheduled = true;
            revealTimer = setTimeout(() => {
                revealTimer = null;
                tryOpenNotice();
            }, INTRO_DOCK_REVEAL_GAP_MS);
        }

        stopWatch = watch(
            () => [
                shellStore.isInteractive,
                contentStore.loaded,
                companyStore.profile,
            ],
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
