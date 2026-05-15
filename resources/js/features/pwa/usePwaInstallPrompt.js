import { computed, onMounted, onUnmounted, ref } from "vue";
import { siteMeta } from "../../config/siteMeta";

/**
 * Chromium PWA install prompt (beforeinstallprompt). iOS не поддерживает — баннер не показываем.
 */
export function usePwaInstallPrompt() {
    const deferredPrompt = ref(null);
    const dismissed = ref(false);

    function readDismissed() {
        if (typeof window === "undefined") {
            return false;
        }
        try {
            return window.localStorage.getItem(siteMeta.pwaInstallDismissKey) === "1";
        } catch {
            return false;
        }
    }

    function persistDismissed() {
        if (typeof window === "undefined") {
            return;
        }
        try {
            window.localStorage.setItem(siteMeta.pwaInstallDismissKey, "1");
        } catch {
            // ignore quota / private mode
        }
    }

    dismissed.value = readDismissed();

    const canInstall = computed(
        () => deferredPrompt.value !== null && !dismissed.value,
    );

    function onBeforeInstallPrompt(event) {
        event.preventDefault();
        deferredPrompt.value = event;
    }

    function onAppInstalled() {
        deferredPrompt.value = null;
        dismissed.value = true;
        persistDismissed();
    }

    function dismiss() {
        dismissed.value = true;
        persistDismissed();
    }

    async function promptInstall() {
        const prompt = deferredPrompt.value;
        if (!prompt) {
            return;
        }

        await prompt.prompt();
        const choice = await prompt.userChoice;
        deferredPrompt.value = null;

        if (choice?.outcome === "accepted") {
            dismissed.value = true;
            persistDismissed();
        }
    }

    onMounted(() => {
        if (typeof window === "undefined") {
            return;
        }

        window.addEventListener("beforeinstallprompt", onBeforeInstallPrompt);
        window.addEventListener("appinstalled", onAppInstalled);
    });

    onUnmounted(() => {
        if (typeof window === "undefined") {
            return;
        }

        window.removeEventListener("beforeinstallprompt", onBeforeInstallPrompt);
        window.removeEventListener("appinstalled", onAppInstalled);
    });

    return {
        canInstall,
        dismiss,
        promptInstall,
    };
}
