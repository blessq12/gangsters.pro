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
