/* Backoffice SPA service worker scaffold. */
self.addEventListener("install", (event) => {
    self.skipWaiting();
    event.waitUntil(Promise.resolve());
});

self.addEventListener("activate", (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener("fetch", () => {
    // pass-through
});
