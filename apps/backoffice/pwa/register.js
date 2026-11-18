/**
 * Register the backoffice SPA PWA.
 */
export function registerPwa() {
    if (!("serviceWorker" in navigator)) {
        return;
    }

    const link = document.createElement("link");
    link.rel = "manifest";
    link.href = "/pwa/backoffice/manifest.webmanifest";
    document.head.appendChild(link);

    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/pwa/backoffice/sw.js", { scope: "/backoffice" })
            .catch(() => {});
    });
}
