/**
 * Register the public SPA PWA.
 */
export function registerPwa() {
    if (!("serviceWorker" in navigator)) {
        return;
    }

    const link = document.createElement("link");
    link.rel = "manifest";
    link.href = "/pwa/public/manifest.webmanifest";
    document.head.appendChild(link);

    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/pwa/public/sw.js", { scope: "/" }).catch(() => {
            // scaffold: ignore registration errors during architecture rebuild
        });
    });
}
