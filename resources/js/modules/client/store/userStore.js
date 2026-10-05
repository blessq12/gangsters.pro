import { defineStore } from "pinia";
import { setClientAuthToken } from "../authToken";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../../platform/domainEvents";
import {
    buildRegisterClientPayload,
    buildLoginClientPayload,
    buildUpdateClientProfilePayload,
    buildClientAddressPayload,
} from "../api";
import {
    registerClientRequest,
    loginClientRequest,
    fetchClientProfileRequest,
    updateClientProfileRequest,
    addClientAddressRequest,
    deleteClientAddressRequest,
    requestPasswordResetRequest,
} from "../api";
import { isAxiosUnauthorized } from "../../../platform/mapApiError";
import { resolvePreferredAddressId } from "../application/resolvePreferredAddressId";

const USER_KEY = "gangsters_user";

function emptyClientProfile() {
    return {
        id: null,
        name: "",
        phone: "",
        email: "",
        birth_date: null,
        created_at: null,
    };
}

/**
 * @param {object|null|undefined} client
 */
function mapClientToProfile(client) {
    return {
        id: client?.id ?? null,
        name: client?.name ?? "",
        phone: client?.phone ?? "",
        email: client?.email ?? "",
        birth_date: client?.birth_date ?? null,
        created_at: client?.created_at ?? null,
    };
}

// --- Payload builders for API contracts ---
export const useUserStore = defineStore("user", {
    state: () => ({
        // Основная информация о клиенте
        profile: emptyClientProfile(),
        // Токен авторизации клиента
        token: null,
        // Выбранный адрес доставки
        selectedAddressId: null,
        // Список адресов клиента
        addresses: [],
    }),
    getters: {
        hasProfile(state) {
            return Boolean(state.profile && (state.profile.name || state.profile.phone));
        },
        selectedAddress(state) {
            if (!state.selectedAddressId) return null;
            return state.addresses.find((a) => a.id === state.selectedAddressId) || null;
        },
    },
    actions: {
        initFromStorage() {
            if (typeof window === "undefined") return;

            try {
                const raw = window.localStorage.getItem(USER_KEY);
                if (!raw) return;

                const parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") return;

                if (parsed.profile && typeof parsed.profile === "object") {
                    this.profile = {
                        ...this.profile,
                        ...parsed.profile,
                    };
                }

                if (Array.isArray(parsed.addresses)) {
                    this.addresses = parsed.addresses;
                }

                if (parsed.selectedAddressId) {
                    this.selectedAddressId = parsed.selectedAddressId;
                }

                this.ensureSelectedAddress({ persist: false, emit: false });

                // Токен последним: один persist уже с полным снимком.
                if (parsed.token) {
                    this.setToken(parsed.token);
                }
            } catch (e) {
                console.error("Failed to init user store from localStorage", e);
            }
        },
        persist() {
            if (typeof window === "undefined") return;

            const payload = {
                profile: this.profile,
                token: this.token,
                addresses: this.addresses,
                selectedAddressId: this.selectedAddressId,
            };

            window.localStorage.setItem(USER_KEY, JSON.stringify(payload));
        },
        setToken(token) {
            this.token = token || null;
            setClientAuthToken(this.token);
            this.persist();
        },
        async clearAuth() {
            this.setToken(null);
            this.profile = emptyClientProfile();
            this.addresses = [];
            this.selectedAddressId = null;
            this.persist();
            emitDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT);
        },
        setProfile(partial) {
            this.profile = {
                ...this.profile,
                ...(partial || {}),
            };
            this.persist();
        },
        setAddresses(addresses) {
            this.addresses = Array.isArray(addresses) ? addresses : [];
            this.ensureSelectedAddress();
        },
        /**
         * Гарантирует выбранный адрес: current → default → первый.
         * @param {{ persist?: boolean, emit?: boolean }} [options]
         * @returns {string|number|null}
         */
        ensureSelectedAddress(options = {}) {
            const persist = options.persist !== false;
            const emit = options.emit !== false;
            const nextId = resolvePreferredAddressId(
                this.addresses,
                this.selectedAddressId,
            );

            if (this.selectedAddressId === nextId) {
                if (persist) {
                    this.persist();
                }
                return nextId;
            }

            this.selectedAddressId = nextId;
            if (persist) {
                this.persist();
            }
            if (emit && nextId != null) {
                emitDomainEvent(DOMAIN_EVENTS.CLIENT_ADDRESS_SELECTED, {
                    id: nextId,
                });
            }

            return nextId;
        },
        upsertAddress(address) {
            if (!address || typeof address !== "object") return;

            const id = address.id ?? Date.now();
            const idx = this.addresses.findIndex((a) => a.id === id);

            if (idx === -1) {
                this.addresses.push({ ...address, id });
            } else {
                this.addresses[idx] = { ...this.addresses[idx], ...address, id };
            }

            this.ensureSelectedAddress();
        },
        removeAddress(id) {
            this.addresses = this.addresses.filter((a) => a.id !== id);
            this.ensureSelectedAddress();
        },
        selectAddress(id) {
            this.selectedAddressId = id;
            this.persist();
            emitDomainEvent(DOMAIN_EVENTS.CLIENT_ADDRESS_SELECTED, { id });
        },
        clear() {
            this.profile = emptyClientProfile();
            this.token = null;
            setClientAuthToken(null);
            this.addresses = [];
            this.selectedAddressId = null;
            if (typeof window !== "undefined") {
                window.localStorage.removeItem(USER_KEY);
            }
            emitDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_OUT);
        },
        // --- API-кейсы клиента ---
        async registerClient(payload) {
            const body = buildRegisterClientPayload(payload);
            const data = await registerClientRequest(body);

            if (data?.client) {
                this.setProfile(mapClientToProfile(data.client));
                if (Array.isArray(data.client.addresses)) {
                    this.setAddresses(data.client.addresses);
                }
            }

            if (data?.token) {
                this.setToken(data.token);
            }

            emitDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_IN, {
                clientId: data?.client?.id ?? null,
            });

            return data;
        },
        async loginClient(credentials) {
            const body = buildLoginClientPayload(credentials);
            const data = await loginClientRequest(body);

            if (data?.client) {
                this.setProfile(mapClientToProfile(data.client));
                if (Array.isArray(data.client.addresses)) {
                    this.setAddresses(data.client.addresses);
                }
            }

            if (data?.token) {
                this.setToken(data.token);
            }

            emitDomainEvent(DOMAIN_EVENTS.CLIENT_LOGGED_IN, {
                clientId: data?.client?.id ?? null,
            });

            return data;
        },
        async fetchClientProfile() {
            if (!this.token) return null;

            // Запоминаем токен на старт: параллельный логин не должен снести новый сеанс
            // из‑за 401 от устаревшего in-flight запроса.
            const tokenAtStart = this.token;

            try {
                const data = await fetchClientProfileRequest();

                if (data?.client) {
                    this.setProfile(mapClientToProfile(data.client));
                    if (Array.isArray(data.client.addresses)) {
                        this.setAddresses(data.client.addresses);
                    }
                }

                emitDomainEvent(DOMAIN_EVENTS.CLIENT_PROFILE_CHANGED, {
                    clientId: data?.client?.id ?? null,
                });

                return data;
            } catch (error) {
                if (isAxiosUnauthorized(error)) {
                    if (this.token === tokenAtStart) {
                        await this.clearAuth();
                    }
                    return null;
                }

                throw error;
            }
        },
        async updateClientProfile(payload) {
            const body = buildUpdateClientProfilePayload(payload);
            const data = await updateClientProfileRequest(body);

            if (data?.client) {
                this.setProfile(mapClientToProfile(data.client));
                if (Array.isArray(data.client.addresses)) {
                    this.setAddresses(data.client.addresses);
                }
            }

            emitDomainEvent(DOMAIN_EVENTS.CLIENT_PROFILE_CHANGED, {
                clientId: data?.client?.id ?? null,
            });

            return data;
        },
        async addClientAddress(payload) {
            const body = buildClientAddressPayload(payload);
            const data = await addClientAddressRequest(body);

            if (data?.client && Array.isArray(data.client.addresses)) {
                this.setAddresses(data.client.addresses);
                if (data.client.default_address_id) {
                    this.selectAddress(data.client.default_address_id);
                } else {
                    this.ensureSelectedAddress();
                }
            }

            emitDomainEvent(DOMAIN_EVENTS.CLIENT_ADDRESS_CREATED, {
                clientId: this.profile.id,
            });

            return data;
        },
        async deleteClientAddress(addressId) {
            const data = await deleteClientAddressRequest(addressId);

            if (data?.client && Array.isArray(data.client.addresses)) {
                this.setAddresses(data.client.addresses);
                if (data.client.default_address_id) {
                    this.selectAddress(data.client.default_address_id);
                } else {
                    this.ensureSelectedAddress();
                }
            }

            emitDomainEvent(DOMAIN_EVENTS.CLIENT_ADDRESS_DELETED, {
                clientId: this.profile.id,
                addressId,
            });

            return data;
        },
        async requestPasswordReset(email) {
            return requestPasswordResetRequest(email);
        },
    },
});
