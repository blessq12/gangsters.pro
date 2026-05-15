import { computed, onMounted, onUnmounted, ref } from "vue";
import { siteMeta } from "../../config/siteMeta";

/**
 * Баннер установки PWA: Chromium (beforeinstallprompt) и подсказка iOS Safari.
 */
export function usePwaInstallBanner() {
    const deferredPrompt = ref(null);
    const dismissed = ref(false);

    function readDismissed() {
        if (typeof window === "undefined") {
            return false;
        }
        try {
            return (
                window.localStorage.getItem(siteMeta.pwaInstallDismissKey) ===
                "1"
            );
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

    function isStandalone() {
        if (typeof window === "undefined") {
            return false;
        }
        if (window.matchMedia("(display-mode: standalone)").matches) {
            return true;
        }
        return Boolean(window.navigator.standalone);
    }

    function isIosSafari() {
        if (typeof navigator === "undefined") {
            return false;
        }
        const ua = navigator.userAgent;
        const isIosDevice =
            /iPad|iPhone|iPod/.test(ua) ||
            (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
        const isSafari =
            /Safari/i.test(ua) &&
            !/CriOS|FxiOS|EdgiOS|Chrome|Chromium/i.test(ua);
        return isIosDevice && isSafari;
    }

    const installMode = computed(() => {
        if (dismissed.value || isStandalone()) {
            return "none";
        }
        if (deferredPrompt.value) {
            return "chromiumInstall";
        }
        if (isIosSafari()) {
            return "iosHint";
        }
        return "none";
    });

    const shouldShowBanner = computed(() => installMode.value !== "none");

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

        if (
            import.meta.env?.DEV &&
            !isStandalone() &&
            !isIosSafari() &&
            !deferredPrompt.value
        ) {
            // Chromium: событие может прийти позже; если нет — проверьте installability в Lighthouse.
            window.setTimeout(() => {
                if (!deferredPrompt.value && !dismissed.value) {
                    console.info(
                        "[PWA] beforeinstallprompt ещё не получен — нужны HTTPS, manifest и критерии installable.",
                    );
                }
            }, 8000);
        }
    });

    onUnmounted(() => {
        if (typeof window === "undefined") {
            return;
        }

        window.removeEventListener("beforeinstallprompt", onBeforeInstallPrompt);
        window.removeEventListener("appinstalled", onAppInstalled);
    });

    return {
        installMode,
        shouldShowBanner,
        dismiss,
        promptInstall,
    };
}

/** @deprecated используйте usePwaInstallBanner */
export function usePwaInstallPrompt() {
    return usePwaInstallBanner();
}
