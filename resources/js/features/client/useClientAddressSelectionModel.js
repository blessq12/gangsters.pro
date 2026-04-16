import { computed } from "vue";
import { useClientCommands } from "./useClientCommands";
import { useClientReadModel } from "./useClientReadModel";
import { mapApiError } from "../../utils/api/mapApiError";

export function useClientAddressSelectionModel() {
    const clientCommands = useClientCommands();
    const clientReadModel = useClientReadModel();

    const selectedAddress = computed(() => clientReadModel.selectedAddress.value);
    const selectedAddressId = computed(
        () => clientReadModel.selectedAddressId.value,
    );
    const addresses = computed(() => clientReadModel.addresses.value);

    async function createAddress(payload) {
        try {
            return await clientCommands.addAddress(payload);
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
        clientCommands.selectAddress(id);
    }

    return {
        addresses,
        selectedAddress,
        selectedAddressId,
        createAddress,
        selectAddress,
    };
}
