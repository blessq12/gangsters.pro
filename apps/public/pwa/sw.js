/* Public SPA service worker scaffold — no business cache strategies yet. */
self.addEventListener("install", (event) => {
    self.skipWaiting();
    event.waitUntil(Promise.resolve());
});

self.addEventListener("activate", (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener("fetch", () => {
    // pass-through; cache strategies are a separate task
});
