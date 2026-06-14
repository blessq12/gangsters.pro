import { computed } from "vue";
import { useUserStore } from "../../stores/userStore";

export function useClientReadModel() {
    const userStore = useUserStore();

    return {
        profile: computed(() => userStore.profile),
        token: computed(() => userStore.token),
        addresses: computed(() => userStore.addresses),
        selectedAddress: computed(() => userStore.selectedAddress),
        selectedAddressId: computed(() => userStore.selectedAddressId),
        isAuthenticated: computed(
            () => Boolean(userStore.token) && Boolean(userStore.profile.id),
        ),
    };
}

export function useClientCommands() {
    const userStore = useUserStore();

    return {
        clearAuth() {
            return userStore.clearAuth();
        },
        fetchProfile() {
            return userStore.fetchClientProfile();
        },
        updateProfile(payload) {
            return userStore.updateClientProfile(payload);
        },
        addAddress(payload) {
            return userStore.addClientAddress(payload);
        },
        deleteAddress(addressId) {
            return userStore.deleteClientAddress(addressId);
        },
        selectAddress(addressId) {
            userStore.selectAddress(addressId);
        },
        login(credentials) {
            return userStore.loginClient(credentials);
        },
        register(payload) {
            return userStore.registerClient(payload);
        },
        requestPasswordReset(email) {
            return userStore.requestPasswordReset(email);
        },
    };
}
