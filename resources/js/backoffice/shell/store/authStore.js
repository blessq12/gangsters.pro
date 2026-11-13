import { defineStore } from "pinia";
import { backofficeRequest } from "../../api";
import { readBackofficeAuthRequired } from "../authConfig";

async function ensureCsrf() {
    await fetch("/sanctum/csrf-cookie", {
        method: "GET",
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
    });
}

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
    },
    actions: {
        async bootstrap() {
            if (this.bootstrapped) {
                return;
            }
            this.loading = true;
            try {
                const payload = await backofficeRequest("GET", "/api/backoffice/auth/me");
                this.employee = payload.employee ?? null;
                if (typeof payload.auth_required === "boolean") {
                    this.authRequired = payload.auth_required;
                }
            } catch {
                this.employee = null;
            } finally {
                this.bootstrapped = true;
                this.loading = false;
            }
        },
        async login(email, password) {
            await ensureCsrf();
            const payload = await backofficeRequest("POST", "/api/backoffice/auth/login", {
                email,
                password,
            });
            this.employee = payload.employee ?? null;
            this.bootstrapped = true;
            return this.employee;
        },
        async logout() {
            try {
                await ensureCsrf();
                await backofficeRequest("POST", "/api/backoffice/auth/logout");
            } finally {
                this.employee = null;
            }
        },
        async requestPasswordReset(email) {
            await ensureCsrf();
            return backofficeRequest("POST", "/api/backoffice/auth/forgot-password", { email });
        },
    },
});
