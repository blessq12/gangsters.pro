import { computed } from "vue";
import { useUserStore } from "../../stores/userStore";
import { mapApiError } from "../../utils/api/mapApiError";

export function useClientAddressSelectionModel() {
    const userStore = useUserStore();

    const selectedAddress = computed(() => userStore.selectedAddress);
    const selectedAddressId = computed(() => userStore.selectedAddressId);
    const addresses = computed(() => userStore.addresses);

    async function createAddress(payload) {
        try {
            return await userStore.addClientAddress(payload);
        } catch (error) {
            throw new Error(
                mapApiError(
                    error,
                    "Не удалось сохранить адрес. Попробуй ещё раз.",
                ),
            );
        }
    }

    function selectAddress(id) {
        userStore.selectAddress(id);
    }

    return {
        addresses,
        selectedAddress,
        selectedAddressId,
        createAddress,
        selectAddress,
    };
}
