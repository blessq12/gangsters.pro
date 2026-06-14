import { defineStore } from "pinia";
import { setClientAuthToken } from "../api/clientAuthToken";
import { DOMAIN_EVENTS, emitDomainEvent } from "../shared/domainEvents";
import {
    buildRegisterClientPayload,
    buildLoginClientPayload,
    buildUpdateClientProfilePayload,
    buildClientAddressPayload,
} from "../api/clientContracts";
import {
    registerClientRequest,
    loginClientRequest,
    fetchClientProfileRequest,
    updateClientProfileRequest,
    addClientAddressRequest,
    deleteClientAddressRequest,
    requestPasswordResetRequest,
    changePasswordWithTokenRequest,
} from "../api/clientApi";
import { isAxiosUnauthorized } from "../utils/api/mapApiError";

const USER_KEY = "gangsters_user";

// --- Payload builders for API contracts ---
export const useUserStore = defineStore("user", {
    state: () => ({
        // Основная информация о клиенте
        profile: {
            id: null,
            name: "",
            phone: "",
            email: "",
        },
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

                // Аккуратно мержим, чтобы не сломать структуру
                if (parsed.profile && typeof parsed.profile === "object") {
                    this.profile = {
                        ...this.profile,
                        ...parsed.profile,
                    };
                }

                if (parsed.token) {
                    this.setToken(parsed.token);
                }

                if (Array.isArray(parsed.addresses)) {
                    this.addresses = parsed.addresses;
                }

                if (parsed.selectedAddressId) {
                    this.selectedAddressId = parsed.selectedAddressId;
                }
            } catch (e) {
                // Если что-то пошло не так — просто не инициализируем из стораджа
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
            this.profile = {
                id: null,
                name: "",
                phone: "",
                email: "",
            };
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
            this.persist();
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

            if (!this.selectedAddressId) {
                this.selectedAddressId = id;
            }

            this.persist();
        },
        removeAddress(id) {
            this.addresses = this.addresses.filter((a) => a.id !== id);
            if (this.selectedAddressId === id) {
                this.selectedAddressId = this.addresses[0]?.id ?? null;
            }
            this.persist();
        },
        selectAddress(id) {
            this.selectedAddressId = id;
            this.persist();
            emitDomainEvent(DOMAIN_EVENTS.CLIENT_ADDRESS_SELECTED, { id });
        },
        clear() {
            this.profile = {
                id: null,
                name: "",
                phone: "",
                email: "",
            };
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
                this.setProfile({
                    id: data.client.id ?? null,
                    name: data.client.name ?? "",
                    phone: data.client.phone ?? "",
                    email: data.client.email ?? "",
                });
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
                this.setProfile({
                    id: data.client.id ?? null,
                    name: data.client.name ?? "",
                    phone: data.client.phone ?? "",
                    email: data.client.email ?? "",
                });
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

            try {
                const data = await fetchClientProfileRequest();

                if (data?.client) {
                    this.setProfile({
                        id: data.client.id ?? null,
                        name: data.client.name ?? "",
                        phone: data.client.phone ?? "",
                        email: data.client.email ?? "",
                    });
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
                    await this.clearAuth();
                    return null;
                }

                throw error;
            }
        },
        async updateClientProfile(payload) {
            const body = buildUpdateClientProfilePayload(payload);
            const data = await updateClientProfileRequest(body);

            if (data?.client) {
                this.setProfile({
                    id: data.client.id ?? null,
                    name: data.client.name ?? "",
                    phone: data.client.phone ?? "",
                    email: data.client.email ?? "",
                });
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
        async changePasswordWithToken({ token, password }) {
            return changePasswordWithTokenRequest({
                token,
                password,
            });
        },
    },
});
