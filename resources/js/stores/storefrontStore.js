import { defineStore } from "pinia";
import {
    fetchStorefrontBootstrapCriticalRequest,
    fetchStorefrontBootstrapDeferredRequest,
} from "../api/storefrontApi";
import {
    applyStorefrontCriticalPayload,
    applyStorefrontDeferredPayload,
} from "../features/storefront/applyStorefrontBootstrap";
import {
    readBootstrapCache,
    writeBootstrapCache,
} from "../features/storefront/bootstrapCache";
import { scheduleIdlePrefetchDockPanels } from "../features/shell/prefetchDockPanels";
import { useShellStore } from "./shellStore";
import { mapApiError } from "../utils/api/mapApiError";

export const useStorefrontStore = defineStore("storefront", {
    state: () => ({
        version: null,
        loaded: false,
        deferredLoaded: false,
        loading: false,
        deferredLoading: false,
        error: null,
        /** @type {Promise<void> | null} */
        inflight: null,
    }),
    actions: {
        hydrateFromCache() {
            const cached = readBootstrapCache();
            if (!cached?.critical) {
                return false;
            }

            this.version = cached.version ?? null;
            applyStorefrontCriticalPayload(cached.critical);
            this.loaded = true;

            if (cached.deferred) {
                applyStorefrontDeferredPayload(cached.deferred);
                this.deferredLoaded = true;
            }

            useShellStore().markDataReady();
            scheduleIdlePrefetchDockPanels();

            return true;
        },

        async fetchBootstrap() {
            if (this.inflight) {
                return this.inflight;
            }

            const hadCache = this.hydrateFromCache();

            this.inflight = this.revalidateBootstrap({ hadCache });
            return this.inflight;
        },

        async revalidateBootstrap({ hadCache = false } = {}) {
            const shellStore = useShellStore();

            if (!hadCache) {
                shellStore.markDataLoading();
                this.loading = true;
            }

            this.error = null;

            try {
                const critical = await fetchStorefrontBootstrapCriticalRequest();
                const nextVersion = critical?.version ?? null;
                const versionChanged = nextVersion !== this.version;

                if (versionChanged) {
                    this.deferredLoaded = false;
                }

                this.version = nextVersion;
                applyStorefrontCriticalPayload(critical);
                this.loaded = true;
                shellStore.markDataReady();

                if (!hadCache || versionChanged) {
                    scheduleIdlePrefetchDockPanels();
                }

                writeBootstrapCache({
                    version: nextVersion,
                    critical,
                    deferred: versionChanged ? null : readBootstrapCache()?.deferred ?? null,
                });

                if (!this.deferredLoaded || versionChanged) {
                    void this.fetchDeferredBootstrap({ version: nextVersion, critical });
                }
            } catch (e) {
                console.error("Failed to fetch storefront critical bootstrap", e);

                if (!this.loaded) {
                    this.error = mapApiError(
                        e,
                        "Не удалось загрузить данные приложения.",
                    );
                    throw e;
                }
            } finally {
                this.loading = false;
                this.inflight = null;
            }
        },

        async fetchDeferredBootstrap({ version, critical } = {}) {
            if (this.deferredLoading) {
                return;
            }

            this.deferredLoading = true;

            try {
                const deferred = await fetchStorefrontBootstrapDeferredRequest();
                applyStorefrontDeferredPayload(deferred);
                this.deferredLoaded = true;

                writeBootstrapCache({
                    version: version ?? this.version,
                    critical: critical ?? readBootstrapCache()?.critical,
                    deferred,
                });
            } catch (e) {
                console.error("Failed to fetch storefront deferred bootstrap", e);
            } finally {
                this.deferredLoading = false;
            }
        },
    },
});
