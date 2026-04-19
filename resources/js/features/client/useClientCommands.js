import { useUserStore } from "../../stores/userStore";

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
