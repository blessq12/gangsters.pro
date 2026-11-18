import { defineStore } from "pinia";
import { request } from "../../http/transport";
import { readBackofficeAuthRequired } from "../authConfig";
import {
    getBackofficeAuthToken,
    persistBackofficeAuthToken,
} from "../authToken";

export const useAuthStore = defineStore("backoffice-auth", {
    state: () => ({
        employee: null,
        authRequired: readBackofficeAuthRequired(),
        bootstrapped: false,
        loading: false,
    }),
    getters: {
        isAuthenticated: (state) => state.employee !== null,
        role: (state) => state.employee?.role ?? null,
        hasToken: () => Boolean(getBackofficeAuthToken()),
    },
    actions: {
        setToken(token) {
            persistBackofficeAuthToken(token || null);
        },
        async bootstrap() {
            if (this.bootstrapped) {
                return;
            }
            this.loading = true;
            try {
                if (!getBackofficeAuthToken()) {
                    this.employee = null;
                    return;
                }
                const payload = await request("GET", "/auth/me");
                this.employee = payload.employee ?? null;
                if (typeof payload.auth_required === "boolean") {
                    this.authRequired = payload.auth_required;
                }
                if (!this.employee) {
                    this.setToken(null);
                }
            } catch {
                this.employee = null;
                this.setToken(null);
            } finally {
                this.bootstrapped = true;
                this.loading = false;
            }
        },
        async login(email, password) {
            const payload = await request("POST", "/auth/login", {
                email,
                password,
            });
            this.setToken(payload.token ?? null);
            this.employee = payload.employee ?? null;
            this.bootstrapped = true;
            return this.employee;
        },
        async logout() {
            try {
                if (getBackofficeAuthToken()) {
                    await request("POST", "/auth/logout");
                }
            } finally {
                this.setToken(null);
                this.employee = null;
            }
        },
        async requestPasswordReset(email) {
            return request("POST", "/auth/forgot-password", { email });
        },
    },
});
